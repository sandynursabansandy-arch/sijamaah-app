<?php

namespace App\Http\Controllers;

use App\Models\KelasMaster;
use App\Models\Santri;
use App\Support\TahunAjar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KelasMasterController extends Controller
{
    /**
     * Buat tabel kelas_master bila belum ada.
     *
     * Shared hosting tidak bisa menjalankan `php artisan migrate`, dan PHP di
     * folder web diblokir, jadi migrasi ini dijalankan sekali lewat halaman ini
     * memakai file migration yang sama (idempotent: IF NOT EXISTS + backfill
     * yang menyantai sudah ada). Setelah tabel terbentuk, fungsi ini hanya
     * melakukan satu query INFORMATION_SCHEMA dan langsung keluar.
     */
    protected function pastikanTabelKelas(): void
    {
        try {
            if (Schema::hasTable('kelas_master')
                && Schema::hasColumn('santris', 'kelas_master_id')
                && Schema::hasColumn('santris', 'kelas_pengajian_id')
                && Schema::hasColumn('santris', 'kelas_sekolah_id')
                && Schema::hasColumn('kelas_master', 'jenis')
                && Schema::hasColumn('kelas_master', 'kapasitas')) {
                return;
            }

            foreach ($this->migrasiKelas() as $file) {
                if (! is_file($file)) {
                    continue;
                }

                $migration = require $file;

                if (is_object($migration) && method_exists($migration, 'up')) {
                    $migration->up();
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Migrasi kelas master, dari yang paling lama.
     *
     * Dipisah dari pemanggilnya karena `bukaTahunAjar` mengurutkan berdasarkan
     * kolom `jenis` — kalau kolom itu belum ada, query-nya akan error.
     *
     * @return array<int, string>
     */
    protected function migrasiKelas(): array
    {
        return [
            database_path('migrations/2026_10_05_000001_create_kelas_master_table.php'),
            database_path('migrations/2026_10_05_000002_add_jenis_to_kelas_master_table.php'),
            database_path('migrations/2026_10_05_000003_add_kelas_pengajian_dan_sekolah_to_santris_table.php'),
            database_path('migrations/2026_10_05_000004_add_kapasitas_to_kelas_master_table.php'),
        ];
    }

    /** Tahun ajar default: yang sedang berjalan, atau terbaru yang ada. */
    protected function tahunAjarAktif(?string $diminta = null): string
    {
        if ($diminta && TahunAjar::valid($diminta)) {
            return $diminta;
        }

        $ada = KelasMaster::daftarTahunAjar();

        return in_array(TahunAjar::sekarang(), $ada, true)
            ? TahunAjar::sekarang()
            : ($ada[0] ?? TahunAjar::sekarang());
    }

    /**
     * Kolom yang menyimpan kelas untuk satu jenis, sebagai pasangan
     * [foreign key, kolom teks].
     *
     * Satu Santri punya dua jalur kelas, jadi naik kelas harus tahu jalur mana
     * yang dipindahkan — memindahkan "Takhosus I" ke "7 SMP" berarti memindahkan
     * kolom pengajian, bukan kolom sekolah.
     *
     * @return array{0: string, 1: string}|null  null bila kolom ganda belum ada
     */
    protected function kolomJalur(KelasMaster $kelas): ?array
    {
        if (!Schema::hasColumn('santris', 'kelas_pengajian_id')) {
            return null;
        }

        return $kelas->isPengajian()
            ? ['kelas_pengajian_id', 'kelas_pengajian']
            : ['kelas_sekolah_id', 'kelas_sekolah'];
    }

    /**
     * Batasan query untuk "Santri yang punya kelas ini pada jalur tertentu".
     *
     * Empat kondisi sekaligus, dari yang paling tegas:
     *   1) foreign key jalur terisi                              → jalur sekolah/pengajian
     *   2) foreign key jalur NULL tapi kolom teks jalur sama      → kelas hasil import
     *      lama atau yang diketik manual di form Santri
     *   3) `kelas_master_id` lama terisi                         → Santri yang dibuat
     *      sebelum kolom ganda ada
     *   4) tidak ada satu pun info jalur, dan kolom `kelas` yang
     *      lama persis sama nama                                  → Santri paling lama
     *
     * Syarat (4) mensyaratkan `kelas_master_id` kosong supaya tidak ikut
     * menangkap Santri yang `kelas`-nya memang menyalin nama ini sebagai
     * cermin jalur LAIN — tanpa syarat itu, kelas sekolah "7 SMP" akan ikut
     * terhitung sebagai anggota kelas pengajian bernama "7 SMP".
     */
    protected function batasSantriKelas($query, KelasMaster $kelas, ?array $jalur = null)
    {
        $jalur = $jalur ?? $this->kolomJalur($kelas);

        if ($jalur === null) {
            return $query->where(function ($q) use ($kelas) {
                $q->where('kelas_master_id', $kelas->id)
                  ->orWhere(fn ($q2) => $q2->whereNull('kelas_master_id')->where('kelas', $kelas->nama));
            });
        }

        [$kolomId, $kolomTeks] = $jalur;

        return $query->where(function ($q) use ($kelas, $kolomId, $kolomTeks) {
            $q->where($kolomId, $kelas->id)
              ->orWhere(function ($q2) use ($kelas, $kolomId, $kolomTeks) {
                  $q2->whereNull($kolomId)
                     ->where(fn ($q3) => $q3->where($kolomTeks, $kelas->nama)
                        ->orWhere(fn ($q4) => $q4->whereNull($kolomTeks)
                           ->whereNull('kelas_master_id')
                           ->where('kelas', $kelas->nama)));
              })
              ->orWhere(fn ($q5) => $q5->where('kelas_master_id', $kelas->id));
        });
    }

    /**
     * Pindahkan seluruh Santri satu jalur kelas ke kelas tujuan.
     *
     * Satu query UPDATE per kolom — bukan per siswa. Yang dipindah hanya jalur
     * kelas asal, jadi kelas sekolah seorang Santri tidak ikut berubah hanya
     * karena kelas pengajiannya naik.
     *
     * Kolom cermin lama (`kelas` / `kelas_master_id`) ikut ditulis supaya halaman
     * & ekspor yang masih membacanya tidak menampilkan nilai lama: saat yang
     * dipindah jalur sekolah, `kelas` jadi nama sekolah terbaru; saat yang
     * dipindah jalur pengajian, `kelas` ditulis ulang HANYA untuk Santri yang
     * belum punya kelas sekolah, supaya kelas sekolah yang sebenarnya tidak
     * tertimpa.
     *
     * @return int  jumlah Santri yang terpindah
     */
    protected function pindahkanSantriKelas(KelasMaster $dari, KelasMaster $ke): int
    {
        $namaBaru = $ke->nama;

        $jalur = $this->kolomJalur($dari);

        if ($jalur === null) {
            // Kolom ganda belum ada: jatuh ke perilaku lama (satu kolom kelas).
            $perubahan = ['kelas_master_id' => $ke->id, 'kelas' => $namaBaru, 'updated_at' => now()];

            return $this->batasSantriKelas(Santri::query(), $dari)->update($perubahan);
        }

        [$kolomId, $kolomTeks] = $jalur;
        $pengajian = $kolomId === 'kelas_pengajian_id';

        $perubahan = [$kolomId => $ke->id, $kolomTeks => $namaBaru, 'updated_at' => now()];

        if (! $pengajian) {
            $perubahan['kelas'] = $namaBaru;
            $perubahan['kelas_master_id'] = $ke->id;
        }

        $terpindah = $this->batasSantriKelas(Santri::query(), $dari, $jalur)->update($perubahan);

        if ($pengajian && Schema::hasColumn('santris', 'kelas_sekolah_id')) {
            // Santri yang belum punya kelas sekolah ikut memakai kolom cermin
            // untuk menampilkan kelas pengajiannya. Yang sudah punya kelas
            // sekolah tidak boleh tertimpa nama pengajian.
            Santri::where($kolomId, $ke->id)
                ->where(fn ($q) => $q->whereNull('kelas_sekolah')->orWhere('kelas_sekolah', ''))
                ->update(['kelas_master_id' => $ke->id, 'kelas' => $namaBaru, 'updated_at' => now()]);
        }

        return $terpindah;
    }

    /**
     * Reposan penunjuk master untuk kelas pengajian yang namanya tidak berubah.
     *
     * Santri tidak naik kelas, tapi tahun ajatnya tetap berganti: "Takhosus I"
     * tahun ini dan tahun depan adalah dua baris `kelas_master` yang BERBEDA.
     * Kalau penukunya dibiarkan menunjuk baris tahun lama, kelas tahun baru
     * terbaca kosong (hasil hitungan dan filter salah) dan kelas tahun lama
     * tetap menghitung Santri yang sebenarnya sudah pindah tahun.
     *
     * Ini bukan kenaikan kelas — hanya pengikutan ke baris master yang benar,
     * jadi tetap dijalankan meski checkbox "pindahkan otomatis" tidak dicentang.
     * Kolom `kelas` lama tidak disentuh karena ia adalah cermin jalur sekolah.
     *
     * @return int  jumlah Santri yang penunjuknya diperbarui
     */
    protected function ikutkanTahunBaru(KelasMaster $lama, KelasMaster $baru): int
    {
        if ($lama->id === $baru->id || ! Schema::hasColumn('santris', 'kelas_pengajian_id')) {
            return 0;
        }

        $perubahan = ['kelas_pengajian_id' => $baru->id, 'updated_at' => now()];

        if (Schema::hasColumn('santris', 'kelas_pengajian')) {
            $perubahan['kelas_pengajian'] = $baru->nama;
        }

        return $this->batasSantriKelas(
            Santri::query(),
            $lama,
            ['kelas_pengajian_id', 'kelas_pengajian']
        )->update($perubahan);
    }

    public function index(Request $request)
    {
        $this->pastikanTabelKelas();

        // Kalau tetap gagal (mis. hak akses DB tidak cukup), tampilkan
        // instruksi, jangan 500. Syaratnya harus sama persis dengan
        // pastikanTabelKelas(): kalau `kapasitas` belum ada tapi tabelnya
        // sudah, halaman ini akan lanjut lalu error saat membaca kolom itu.
        if (!Schema::hasTable('kelas_master')
            || !Schema::hasColumn('kelas_master', 'kapasitas')
            || !Schema::hasColumn('santris', 'kelas_master_id')
            || !Schema::hasColumn('santris', 'kelas_pengajian_id')
            || !Schema::hasColumn('santris', 'kelas_sekolah_id')) {
            return view('kelas.index', [
                'kelas' => collect(),
                'tahunAjar' => TahunAjar::sekarang(),
                'daftarTahunAjar' => [TahunAjar::sekarang()],
                'totalSantri' => 0,
                'saranKelasBerikut' => [],
                'tahunAjarBerikut' => TahunAjar::berikutnya(TahunAjar::sekarang()),
                'kelasPerTahunAjar' => collect(),
                'belumDimigrasi' => true,
                'kelasPengajian' => collect(),
                'kelasSekolah' => collect(),
                'jumlahPengajian' => 0,
                'jumlahSekolah' => 0,
                'kapasitasPengajian' => 0,
            ]);
        }

        $tahunAjar = $this->tahunAjarAktif($request->get('tahun_ajar'));

        $kelas = KelasMaster::withCount('santrisPengajian')
            ->withCount('santrisSekolah')
            ->tahunAjar($tahunAjar)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        // Dua jalur ditampilkan terpisah supaya jelas mana yang boleh naik
        // sendiri. Kelas pengajian diurutkan menurut urut pengajian (Kelas 1-5,
        // lalu Takhosus I/II) karena tidak punya tingkat resmi.
        $kelasPengajian = collect(KelasMaster::urutPengajian($kelas->filter->isPengajian()->all()))
            ->values();
        $kelasSekolah = $kelas->reject->isPengajian()->values();

        // Tahun ajar lain yang punya siswa, supaya tidak kehujur.
        $tahunAjarLain = DB::table('santris')
            ->join('kelas_master', 'kelas_master.id', '=', 'santris.kelas_master_id')
            ->whereNotNull('santris.kelas_master_id')
            ->distinct()
            ->orderByDesc('kelas_master.tahun_ajar')
            ->pluck('kelas_master.tahun_ajar')
            ->all();

        $daftarTahunAjar = array_values(array_unique(array_merge(
            [$tahunAjar],
            $tahunAjarLain,
            [TahunAjar::sekarang()]
        )));
        rsort($daftarTahunAjar);

        $totalSantri = $this->hitungSantriTotal($tahunAjar);

        // Saran kelas berikutnya untuk wizard "Naik Kelas".
        $saran = [];
        foreach ($kelas as $k) {
            $berikut = $k->namaKelasBerikutnya();
            if ($berikut) {
                $saran[$k->id] = $berikut;
            }
        }

        // Semua kelas dari semua tahun ajar, dikelompokkan per tahun — dipakai
        // dropdown Kelas Asal & Kelas Tujuan di wizard. Diambil sekali di sini
        // supaya blade tidak query database per tahun ajar.
        $kelasPerTahunAjar = KelasMaster::query()
            ->orderByDesc('tahun_ajar')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->groupBy('tahun_ajar');

        return view('kelas.index', [
            'kelas' => $kelas,
            'kelasPengajian' => $kelasPengajian,
            'kelasSekolah' => $kelasSekolah,
            'jumlahPengajian' => (int) $kelas->sum('santris_pengajian_count'),
            'jumlahSekolah' => (int) $kelas->sum('santris_sekolah_count'),
            'kapasitasPengajian' => (int) $kelasPengajian->sum(fn ($k) => (int) $k->kapasitasEfektif()),
            'tahunAjar' => $tahunAjar,
            'daftarTahunAjar' => $daftarTahunAjar,
            'totalSantri' => $totalSantri,
            'saranKelasBerikut' => $saran,
            'tahunAjarBerikut' => TahunAjar::berikutnya($tahunAjar),
            'kelasPerTahunAjar' => $kelasPerTahunAjar,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'tahun_ajar' => 'required|string|max:20',
            'jenis' => 'nullable|string|max:10',
            'kapasitas' => 'nullable|integer|min:1|max:500',
        ], [
            'nama.required' => 'Nama kelas wajib diisi.',
            'nama.max' => 'Nama kelas maksimal 100 karakter.',
            'tahun_ajar.required' => 'Tahun ajar wajib diisi.',
            'tahun_ajar.max' => 'Format tahun ajar tidak valid.',
            'jenis.in' => 'Jenis kelas harus formal atau pengajian.',
            'kapasitas.min' => 'Jumlah ideal kelas minimal 1 orang.',
            'kapasitas.max' => 'Jumlah ideal kelas maksimal 500 orang.',
        ]);

        if (!TahunAjar::valid($data['tahun_ajar'])) {
            return back()->with('error', 'Format tahun ajar harus "2026/2027".');
        }

        $nama = trim($data['nama']);

        $bentrok = KelasMaster::tahunAjar($data['tahun_ajar'])
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])
            ->exists();

        if ($bentrok) {
            return back()->with('error', 'Kelas "' . $nama . '" sudah ada di tahun ajar ' . $data['tahun_ajar'] . '.');
        }

        $p = TahunAjar::parseNamaKelas($nama);

        // Jenis boleh dipilih manual di form; kalau tidak diisi, tebak dari
        // namanya ("Takhosus I", "1" → pengajian; "1 SMP" → formal).
        $jenis = isset($data['jenis']) && TahunAjar::jenisValid($data['jenis'])
            ? $data['jenis']
            : TahunAjar::deteksiJenis($nama);

        KelasMaster::create([
            'tahun_ajar' => $data['tahun_ajar'],
            'nama' => $nama,
            'jenjang' => $p['jenjang'],
            'tingkat' => $p['tingkat'],
            'sisa' => $p['sisa'] !== '' ? $p['sisa'] : null,
            'jenis' => $jenis,
            'kapasitas' => $jenis === KelasMaster::PENGAJIAN
                ? ($data['kapasitas'] ?? KelasMaster::KAPASITAS_PENGAJIAN)
                : null,
            'aktif' => true,
        ]);

        return back()->with('success', sprintf(
            'Kelas "%s" ditambahkan ke %s sebagai %s.',
            $nama,
            $data['tahun_ajar'],
            $jenis === KelasMaster::PENGAJIAN ? 'kelas pengajian (tidak naik kelas)' : 'kelas formal'
        ));
    }

    public function update(Request $request, KelasMaster $kelas)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'jenis' => 'nullable|string|max:10',
            'kapasitas' => 'nullable|integer|min:1|max:500',
        ], [
            'nama.required' => 'Nama kelas wajib diisi.',
            'jenis.in' => 'Jenis kelas harus formal atau pengajian.',
            'kapasitas.min' => 'Jumlah ideal kelas minimal 1 orang.',
            'kapasitas.max' => 'Jumlah ideal kelas maksimal 500 orang.',
        ]);

        if (!Schema::hasColumn('santris', 'kelas_master_id')) {
            return back()->with('error', 'Kolom kelas_master_id belum tersedia. Jalankan migrasi database terlebih dahulu.');
        }

        $namaBaru = trim($data['nama']);

        $bentrok = KelasMaster::tahunAjar($kelas->tahun_ajar)
            ->whereKeyNot($kelas->id)
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower($namaBaru)])
            ->exists();

        if ($bentrok) {
            return back()->with('error', 'Kelas "' . $namaBaru . '" sudah ada di tahun ajar ' . $kelas->tahun_ajar . '.');
        }

        $p = TahunAjar::parseNamaKelas($namaBaru);
        $namaLama = $kelas->nama;

        // Kalau nama kelas diubah, jenisnya ikut ditebak ulang — tapi HANYA
        // bila sebelumnya nilai itu hasil tebakan otomatis. Kelas yang
        // jenisnya sudah dipilih manual lewat toggle tidak ditimpa.
        $perubahan = ['nama' => $namaBaru, 'jenjang' => $p['jenjang'],
            'tingkat' => $p['tingkat'], 'sisa' => $p['sisa'] !== '' ? $p['sisa'] : null];

        if ($namaBaru !== $namaLama) {
            $jenisLama = $kelas->jenis ?: TahunAjar::deteksiJenis($namaLama);

            if ($jenisLama === TahunAjar::deteksiJenis($namaLama)) {
                $perubahan['jenis'] = TahunAjar::deteksiJenis($namaBaru);
            }
        }

        // Jumlah ideal orang per kelas hanya relevan untuk pengajian (±20 orang).
        // Kelas sekolah dibiarkan kosong supaya tabelnya tidak menampilkan
        // batas yang tidak ada artinya.
        if (array_key_exists('kapasitas', $data)) {
            $kapasitas = $data['kapasitas'] !== null && $data['kapasitas'] !== ''
                ? (int) $data['kapasitas']
                : null;

            $perubahan['kapasitas'] = $kapasitas !== null && ! $kelas->isPengajian()
                ? null
                : $kapasitas;
        }

        DB::transaction(function () use ($kelas, $namaBaru, $perubahan) {
            $kelas->update($perubahan);

            // Kolom teks pada Santri ikut menyesuaikan supaya cetak/Excel/FDF
            // benar — tapi hanya kolom jalur yang sesuai jenis kelas ini. Kelas
            // sekolah Santri tidak boleh ikut berubah karena nama kelas
            // pengajiannya disunting.
            $jalur = $this->kolomJalur($kelas);

            if ($jalur === null) {
                Santri::where('kelas_master_id', $kelas->id)
                    ->update(['kelas' => $namaBaru, 'updated_at' => now()]);

                return;
            }

            [$kolomId, $kolomTeks] = $jalur;

            Santri::where($kolomId, $kelas->id)
                ->update([$kolomTeks => $namaBaru, 'updated_at' => now()]);

            if ($kolomId === 'kelas_sekolah_id') {
                Santri::where('kelas_master_id', $kelas->id)
                    ->update(['kelas' => $namaBaru, 'updated_at' => now()]);
            } else {
                // Santri tanpa kelas sekolah ikut memakai kolom cermin.
                Santri::where($kolomId, $kelas->id)
                    ->where(fn ($q) => $q->whereNull('kelas_sekolah')->orWhere('kelas_sekolah', ''))
                    ->update(['kelas' => $namaBaru, 'updated_at' => now()]);
            }
        });

        $tambahan = isset($perubahan['jenis'])
            ? ' Jenis kelas disesuaikan menjadi ' . ($perubahan['jenis'] === KelasMaster::PENGAJIAN ? 'Pengajian (tidak naik kelas).' : 'Formal.')
            : '';

        return back()->with('success', 'Kelas "' . $namaLama . '" diubah menjadi "' . $namaBaru . '".' . $tambahan);
    }

    public function destroy(KelasMaster $kelas)
    {
        if ($kelas->santris()->exists()) {
            return back()->with('error', 'Kelas "' . $kelas->nama . '" masih punya Santri. Pindahkan dulu mereka sebelum menghapus.');
        }

        $nama = $kelas->nama;
        $kelas->delete();

        return back()->with('success', 'Kelas "' . $nama . '" dihapus.');
    }

    /**
     * Naik kelas: pindahkan SELURUH siswa satu kelas ke kelas lain.
     * Satu query UPDATE per kelas — bukan per siswa.
     */
    public function naikKelas(Request $request)
    {
        $data = $request->validate([
            'dari_kelas_id' => 'required|integer|exists:kelas_master,id',
            'ke_kelas_id' => 'required|integer|exists:kelas_master,id',
        ], [
            'dari_kelas_id.required' => 'Pilih kelas asal.',
            'ke_kelas_id.required' => 'Pilih kelas tujuan.',
        ]);

        $dari = KelasMaster::findOrFail($data['dari_kelas_id']);
        $ke = KelasMaster::findOrFail($data['ke_kelas_id']);

        if ($dari->id === $ke->id) {
            return back()->with('error', 'Kelas asal dan tujuan tidak boleh sama.');
        }

        if (!Schema::hasColumn('santris', 'kelas_master_id')) {
            return back()->with('error', 'Kolom kelas_master_id belum tersedia. Jalankan migrasi database terlebih dahulu.');
        }

        $jumlah = $this->hitungSantri($dari);

        // Memindahkan kelas pengajian ke kelas sekolah (atau sebaliknya) berarti
        // menukar orang, bukan menukar kelas. Dicegah di sini, sebelum data
        // Santri terlanjur tersusun campur.
        if (Schema::hasColumn('santris', 'kelas_pengajian_id')
            && $dari->isPengajian() !== $ke->isPengajian()) {
            return back()->with('error', sprintf(
                'Kelas pengajian "%s" dan kelas sekolah "%s" berbeda jenis. '
                . 'Ubah tiap jalur lewat form Ubah Santri supaya jelas kelas pengajian dan kelas sekolahnya jadi apa.',
                $dari->nama,
                $ke->nama
            ));
        }

        if ($jumlah === 0) {
            return back()->with('error', 'Kelas "' . $dari->nama . '" tidak punya Santri untuk dipindahkan.');
        }

        DB::transaction(fn () => $this->pindahkanSantriKelas($dari, $ke));

        return back()->with('success', sprintf(
            '%d Santri dari kelas %s "%s" dipindahkan ke kelas %s "%s".',
            $jumlah,
            $dari->labelJenis(),
            $dari->nama,
            $ke->labelJenis(),
            $ke->nama
        ));
    }

    /**
     * Naikkan kelas pengajian untuk orang-orang yang dipilih saja.
     *
     * Kelas pengajian tidak bisa dinaikkan per kelas, karena tidak semua orang
     * di dalamnya layak naik. Guru mencentang siapa yang siap, lalu sisanya
     * tetap di kelas asalnya — persis seperti "naik kalau benar-benar paham
     * atau layak naik".
     *
     * Yang dipindah HANYA kolom jalur pengajian. Kelas sekolah Santri yang
     * sama tidak boleh ikut berubah; contoh Dzikra Zaedan naik dari pengajian
     * Takhosus I ke Takhosus II, sementara sekolahnya tetap 2 SMA.
     */
    public function naikPengajian(Request $request)
    {
        $this->pastikanTabelKelas();

        $data = $request->validate([
            'dari_kelas_id' => 'required|integer|exists:kelas_master,id',
            'ke_kelas_id' => 'required|integer|exists:kelas_master,id',
            'santri_ids' => 'required|array|min:1',
            'santri_ids.*' => 'integer|distinct|exists:santris,id',
        ], [
            'dari_kelas_id.required' => 'Pilih kelas pengajian asal.',
            'ke_kelas_id.required' => 'Pilih kelas pengajian tujuan.',
            'santri_ids.required' => 'Centang minimal satu Santri yang akan naik.',
            'santri_ids.*.exists' => 'Ada Santri yang sudah tidak ada.',
        ]);

        if (! Schema::hasColumn('santris', 'kelas_pengajian_id')) {
            return back()->with('error', 'Kolom kelas pengajian belum tersedia. Buka halaman ini lagi untuk menjalankan migrasi.');
        }

        $dari = KelasMaster::findOrFail($data['dari_kelas_id']);
        $ke = KelasMaster::findOrFail($data['ke_kelas_id']);

        if ($dari->id === $ke->id) {
            return back()->with('error', 'Kelas asal dan tujuan tidak boleh sama.');
        }

        // Kenaikan kelas pengajian hanya antar kelas pengajian. Kelas sekolah
        // punya aturan otomatis sendiri, dan mencampur keduanya berarti
        // menukar orang, bukan menukar kelas.
        if (! $dari->isPengajian() || ! $ke->isPengajian()) {
            return back()->with('error', sprintf(
                'Kenaikan kelas pengajian hanya boleh antar kelas pengajian, "%s" dan "%s" bukan pasangan yang cocok.',
                $dari->nama,
                $ke->nama
            ));
        }

        $ids = array_values(array_unique(array_map('intval', $data['santri_ids'])));

        // Hanya Santri yang benar-benar anggota kelas asal yang boleh dipindah.
        // Kalau ada yang dicentang dari kelas lain, tolak seluruhnya supaya
        // admin tidak mengira orang itu sudah naik padahal tidak.
        $terpilih = $this->batasSantriKelas(
            Santri::query()->whereIn('id', $ids),
            $dari,
            ['kelas_pengajian_id', 'kelas_pengajian']
        )->pluck('id')->map(fn ($id) => (int) $id)->all();

        $asing = array_diff($ids, $terpilih);

        if ($asing) {
            return back()->with('error', sprintf(
                '%d Santri yang dicentang bukan anggota kelas pengajian "%s". Refresh halaman lalu coba lagi.',
                count($asing),
                $dari->nama
            ));
        }

        if (! $terpilih) {
            return back()->with('error', 'Kelas pengajian "' . $dari->nama . '" tidak punya Santri yang dipilih.');
        }

        $perubahan = [
            'kelas_pengajian_id' => $ke->id,
            'kelas_pengajian' => $ke->nama,
            'updated_at' => now(),
        ];

        Santri::whereIn('id', $terpilih)->update($perubahan);

        // Santri yang belum punya kelas sekolah memakai kolom cermin `kelas` untuk
        // menampilkan kelas pengajiannya, jadi ikut disesuaikan supaya cetak dan
        // ekspor tidak menampilkan kelas pengajian yang sudah ditinggalkan.
        if (Schema::hasColumn('santris', 'kelas_sekolah_id')) {
            Santri::whereIn('id', $terpilih)
                ->where(fn ($q) => $q->whereNull('kelas_sekolah')->orWhere('kelas_sekolah', ''))
                ->update(['kelas_master_id' => $ke->id, 'kelas' => $ke->nama, 'updated_at' => now()]);
        }

        $sisaKursi = $ke->sisaKursi();
        $pesan = sprintf(
            '%d Santri naik dari "%s" ke "%s". %d orang tetap di "%s".',
            count($terpilih),
            $dari->nama,
            $ke->nama,
            $this->hitungSantri($dari),
            $dari->nama
        );

        // Penuh bukan error: di pengajian memang bisa lebih dari patokan karena
        // ada yang belum layak naik. Cukup diberi tahu supaya terlihat.
        if ($sisaKursi !== null && $sisaKursi <= 0) {
            $pesan .= sprintf(
                ' Perhatian: "%s" sekarang lewat patokan %d orang — jelas berarti ada yang belum naik.',
                $ke->nama,
                (int) $ke->kapasitasEfektif()
            );
        }

        return back()->with('success', $pesan);
    }

    /**
     * Buka tahun ajar baru: salin master kelas tahun lama dengan tingkat
     * naik satu, lalu opsional pindahkan semua Santri dalam 1 transaksi.
     */
    public function bukaTahunAjar(Request $request)
    {
        $data = $request->validate([
            'dari_tahun_ajar' => 'required|string|max:20',
            'ke_tahun_ajar' => 'required|string|max:20',
            'pindahkan_santri' => 'nullable|boolean',
        ], [
            'dari_tahun_ajar.required' => 'Pilih tahun ajar asal.',
            'ke_tahun_ajar.required' => 'Pilih tahun ajar tujuan.',
        ]);

        $dari = trim($data['dari_tahun_ajar']);
        $ke = trim($data['ke_tahun_ajar']);

        if (!TahunAjar::valid($dari) || !TahunAjar::valid($ke)) {
            return back()->with('error', 'Format tahun ajar harus "2026/2027".');
        }

        if ($dari === $ke) {
            return back()->with('error', 'Tahun ajar asal dan tujuan tidak boleh sama.');
        }

        $this->pastikanTabelKelas();

        // Pengajian disalin lebih dulu, lalu kelas formal dari tingkat
        // TERTINGGI ke terendah. Kalau diproses dari bawah, Santri sudah naik
        // dua tingkat dalam satu kali klik karena kelas tujuan yang baru
        // dibuat ikut tersalin lagi.
        $sumber = KelasMaster::tahunAjar($dari)
            ->orderByRaw('jenis = ? DESC', [KelasMaster::PENGAJIAN])
            ->orderByDesc('tingkat')
            ->orderBy('nama')
            ->get();

        if ($sumber->isEmpty()) {
            return back()->with('error', 'Tahun ajar ' . $dari . ' belum punya master kelas.');
        }

        if (KelasMaster::tahunAjar($ke)->exists()) {
            return back()->with('error', 'Tahun ajar ' . $ke . ' sudah punya master kelas. Hapus dulu atau pakai wizard Naik Kelas.');
        }

        // Kalau kelas formal akan naik ke nama yang sudah dipakai kelas
        // pengajian (mis. kelas "1" formal naik ke "2", sementara "2" adalah
        // pengajian), dua kelompok Santri akan tercampur dalam satu kelas.
        // Lebih baik berhenti di sini daripada diam-diam salah campur.
        $namaPengajian = $sumber->filter->isPengajian()
            ->map(fn($k) => mb_strtolower($k->nama))->all();

        $bentrok = [];
        foreach ($sumber as $k) {
            if ($k->isPengajian()) {
                continue;
            }

            $tujuan = $k->namaKelasBerikutnya();
            if ($tujuan !== null && in_array(mb_strtolower($tujuan), $namaPengajian, true)) {
                $bentrok[] = $k->nama . ' → ' . $tujuan;
            }
        }

        if ($bentrok) {
            return back()->with('error',
                'Nama kelas tujuan bentrok dengan kelas pengajian: ' . implode(', ', $bentrok)
                . '. Ubah jenis kelas yang bertanda Formal menjadi Pengajian di halaman ini, atau ganti namanya.'
            );
        }

        $pindahkan = $request->boolean('pindahkan_santri');
        $dibuat = 0;
        $terpindah = 0;
        $dilewati = [];
        $tetap = 0;
        $naik = 0;
        $sisaPengajian = [];

        DB::transaction(function () use ($sumber, $dari, $ke, $pindahkan, &$dibuat, &$terpindah, &$dilewati, &$tetap, &$naik, &$sisaPengajian) {
            foreach ($sumber as $k) {
                $pengajian = $k->isPengajian();

                // Kelas pengajian: nama dipertahankan dan Santri tetap di
                // kelas yang sama. Kelas formal: naik satu tingkat.
                $namaBaru = $pengajian ? $k->nama : $k->namaKelasBerikutnya();

                if ($namaBaru === null) {
                    // Sudah tingkat tertinggi / tidak bisa ditebak: pakai nama
                    // yang sama supaya admin tinggal menyunting.
                    $namaBaru = $k->nama;
                    $dilewati[] = $k->nama . ' (tingkat tidak bisa dinaikkan otomatis, disalin dengan nama sama)';
                } elseif ($pengajian) {
                    $tetap++;
                } else {
                    $naik++;
                }

                $p = TahunAjar::parseNamaKelas($namaBaru);

                // firstOrCreate: kelas tanpa nama التالية bisa berm namesake dengan
                // kelas yang baru dibuat tingkat di bawahnya (mis. "12 SMA" disalin
                // apa adanya sementara "11 SMA" sudah Creates "12 SMA"). Pakai yang
                // sudah ada daripada gagal di tengah transaksi.
                $baru = KelasMaster::firstOrCreate(
                    ['tahun_ajar' => $ke, 'nama' => $namaBaru],
                    $pengajian
                        // Salin jenjang/tingkat/sisa dari kelas asal supaya nilai
                        // yang sudah disunting manual tidak hilang.
                        ? ['jenjang' => $k->jenjang, 'tingkat' => $k->tingkat, 'sisa' => $k->sisa,
                            'jenis' => KelasMaster::PENGAJIAN, 'aktif' => true]
                        : ['jenjang' => $p['jenjang'], 'tingkat' => $p['tingkat'],
                            'sisa' => $p['sisa'] !== '' ? $p['sisa'] : null,
                            'jenis' => KelasMaster::FORMAL, 'aktif' => true]
                );

                if ($baru->wasRecentlyCreated) {
                    $dibuat++;
                }

                if ($pengajian) {
                    // Kelas pengajian SENGAJA tidak naik. Namanya sama persis,
                    // jadi tidak ada keputusan "naik atau tinggal" yang perlu
                    // menunggu guru — yang perlu diperbarui hanya penunjuk
                    // master-nya, supaya kelas tahun baru punya anggota yang
                    // benar (lihat ikutkanTahunBaru()).
                    $jumlah = $this->ikutkanTahunBaru($k, $baru);
                    if ($jumlah > 0) {
                        $sisaPengajian[$k->nama] = $jumlah;
                    }

                    continue;
                }

                if (! $pindahkan) {
                    continue;
                }

                $terpindah += $this->pindahkanSantriKelas($k, $baru);
            }

            // Tahun ajar lama ditandai tidak aktif.
            if (Schema::hasColumn('kelas_master', 'aktif')) {
                KelasMaster::tahunAjar($dari)->update(['aktif' => false]);
            }
        });

        $pesan = sprintf('Tahun ajar %s dibuat: %d kelas baru.', $ke, $dibuat);

        if ($naik > 0) {
            $pesan .= sprintf(' %d kelas sekolah naik satu tingkat.', $naik);
        }

        if ($pindahkan) {
            $pesan .= sprintf(' %d Santri dipindahkan ke kelas sekolah barunya.', $terpindah);
        } else {
            $pesan .= ' Santri belum dipindahkan — pakai wizard Naik Kelas bila perlu.';
        }

        $totalPengajian = array_sum($sisaPengajian);

        if ($tetap > 0) {
            $pesan .= sprintf(' %d kelas pengajian disalin dengan nama sama.', $tetap);
        }

        if ($totalPengajian > 0) {
            $detail = [];
            foreach ($sisaPengajian as $nama => $jumlah) {
                $detail[] = $nama . ' (' . $jumlah . ')';
            }

            $pesan .= sprintf(
                ' %d Santri pengajian TETAP di kelasnya yang sama (tidak naik kelas). '
                . 'Kelas pengajian tidak dinaikkan otomatis dan tidak semua orangnya naik, '
                . 'jadi yang naik harus dipilih per kelas lewat tombol Naik.',
                $totalPengajian
            );
            $pesan .= ' Kelas: ' . implode(', ', $detail) . '.';
        }

        if ($dilewati) {
            $pesan .= ' Perlu disunting manual: ' . implode('; ', $dilewati) . '.';
        }

        return redirect()
            ->route('kelas.index', ['tahun_ajar' => $ke])
            ->with('success', $pesan);
    }

    /**
     * Ubah jenis kelas (pengajian / formal) tanpa menyunting nama.
     *
     * Dipakai toggle kecil di tabel kelas: yang berubah hanya kolom `jenis`,
     * jadi satu klik sudah cukup dan tidak risking nama kelas atau data Santri.
     */
    public function jenis(Request $request, KelasMaster $kelas)
    {
        $data = $request->validate([
            'jenis' => 'required|string|max:10',
        ], [
            'jenis.required' => 'Pilih jenis kelas.',
        ]);

        $jenis = strtolower(trim($data['jenis']));

        if (!TahunAjar::jenisValid($jenis)) {
            return back()->with('error', 'Jenis kelas harus "formal" atau "pengajian".');
        }

        $kelas->update(['jenis' => $jenis]);

        return back()->with('success', sprintf(
            'Kelas "%s" (%s) ditandai sebagai %s.',
            $kelas->nama,
            $kelas->tahun_ajar,
            $jenis === KelasMaster::PENGAJIAN
                ? 'kelas pengajian — tidak naik kelas tiap tahun'
                : 'kelas formal — naik satu tingkat tiap tahun'
        ));
    }

    /** Preview jumlah Santri pada sebuah kelas (untuk wizard). */
    public function pratinjau(Request $request, KelasMaster $kelas): JsonResponse
    {
        $pengajian = $kelas->isPengajian();

        return response()->json([
            'kelas' => $kelas->nama,
            'tahun_ajar' => $kelas->tahun_ajar,
            'jumlah' => $this->hitungSantri($kelas),
            'berikutnya' => $pengajian ? null : $kelas->namaKelasBerikutnya(),
            'jenis' => $pengajian ? KelasMaster::PENGAJIAN : KelasMaster::FORMAL,
            'label_jenis' => $kelas->labelJenis(),
        ]);
    }

    /**
     * Daftar anggota kelas pengajian, untuk panel kenaikan kelas pengajian.
     *
     * Hanya kelas pengajian yang dilayani. Kelas sekolah naik otomatis saat
     * tahun ajar dibuka, jadi tidak ada yang perlu dicentang manual. Endpoint ini
     * ini ada supaya halaman /kelas tidak perlu memuat seluruh roster (yang
     * bisa ratusan orang) sekaligus.
     */
    public function anggota(Request $request, KelasMaster $kelas): JsonResponse
    {
        if (! $kelas->isPengajian()) {
            return response()->json([
                'kelas' => $kelas->nama,
                'tahun_ajar' => $kelas->tahun_ajar,
                'jenis' => $kelas->isPengajian() ? KelasMaster::PENGAJIAN : KelasMaster::FORMAL,
                'anggota' => [],
                'pesan' => 'Kelas sekolah naik otomatis. Panel ini hanya untuk kelas pengajian.',
            ]);
        }

        $jalur = ['kelas_pengajian_id', 'kelas_pengajian'];

        $anggota = $this->batasSantriKelas(
            Santri::query()->with('kamar')->orderBy('nama'),
            $kelas,
            $jalur
        )
            ->get()
            ->map(fn ($s) => [
                'id' => (int) $s->id,
                'nama' => $s->nama,
                'kamar' => optional($s->kamar)->nama_kamar,
                // Kelas sekolah ikut ditampilkan supaya guru bisa melihat
                // konteks orangnya: ada yang sudah 2 SMA tapi pengajiannya
                // masih Kelas 2.
                'kelas_sekolah' => $s->kelas_sekolah,
            ])
            ->values();

        return response()->json([
            'kelas' => $kelas->nama,
            'tahun_ajar' => $kelas->tahun_ajar,
            'jenis' => KelasMaster::PENGAJIAN,
            'kapasitas' => $kelas->kapasitasEfektif(),
            'jumlah' => $anggota->count(),
            'anggota' => $anggota,
        ]);
    }

    /** Hitung Santri pada kelas, termasuk yang belum punya foreign key jalur. */
    protected function hitungSantri(KelasMaster $kelas): int
    {
        return $this->batasSantriKelas(Santri::query(), $kelas)->count();
    }

    /**
     * Jumlah Santri unik yang punya kelas dari tahun ajar tertentu, di kedua
     * jalur sekaligus.
     *
     * `distinct` dipakai karena satu Santri bisa terhitung dua kali — sekali
     * lewat kelas pengajiannya, sekali lewat kelas sekolahnya.
     */
    protected function hitungSantriTotal(string $tahunAjar): int
    {
        if (!Schema::hasColumn('santris', 'kelas_pengajian_id')) {
            return (int) Santri::whereHas('kelasMaster', fn($q) => $q->where('tahun_ajar', $tahunAjar))->count();
        }

        $ids = KelasMaster::tahunAjar($tahunAjar)->pluck('id');

        return Santri::whereIn('kelas_pengajian_id', $ids)
            ->orWhereIn('kelas_sekolah_id', $ids)
            ->orWhereIn('kelas_master_id', $ids)
            ->distinct()
            ->count();
    }
}
