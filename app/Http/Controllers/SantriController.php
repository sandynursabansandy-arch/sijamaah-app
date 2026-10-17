<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\Kamar;
use App\Models\KelasMaster;
use App\Support\SchemaCache;
use App\Support\TahunAjar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SantriController extends Controller
{
    protected function getAddressRules(): array
    {
        return [
            'no_whatsapp' => 'nullable|string|max:20',
            'alamat_provinsi' => 'nullable|string|max:50',
            'alamat_kabupaten' => 'nullable|string|max:50',
            'alamat_kecamatan' => 'nullable|string|max:50',
            'alamat_desa' => 'nullable|string|max:50',
            'alamat_dusun' => 'nullable|string|max:50',
            'alamat_rt' => 'nullable|string|max:5',
            'alamat_rw' => 'nullable|string|max:5',
            'alamat_jalan' => 'nullable|string|max:100',
            'alamat_nomor_rumah' => 'nullable|string|max:20',
            'kode_pos' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'foto_rumah' => 'nullable|image|max:2048',
            'foto_rumah_consent' => 'nullable|in:1',
        ];
    }

    protected function getAddressData(Request $request): array
    {
        $columns = [
            'no_whatsapp', 'alamat_provinsi', 'alamat_kabupaten',
            'alamat_kecamatan', 'alamat_desa', 'alamat_dusun',
            'alamat_rt', 'alamat_rw', 'alamat_jalan', 'alamat_nomor_rumah',
            'kode_pos', 'latitude', 'longitude',
        ];

        $data = [];
        foreach ($columns as $col) {
            if (Schema::hasColumn('santris', $col) && $request->has($col)) {
                $data[$col] = $request->input($col);
            }
        }

        if (Schema::hasColumn('santris', 'foto_rumah') && $request->hasFile('foto_rumah')) {
            $path = $request->file('foto_rumah')->store('foto-rumah', 'public');
            $data['foto_rumah'] = $path;
        }

        if (Schema::hasColumn('santris', 'alamat_lengkap')) {
            $data['alamat_lengkap'] = $this->buildAlamatLengkap($request);
        }

        return $data;
    }

    protected function buildAlamatLengkap(Request $request): string
    {
        $parts = [
            $request->input('alamat_jalan'),
            $request->input('alamat_nomor_rumah'),
            $request->filled('alamat_rt') ? 'RT ' . $request->input('alamat_rt') : null,
            $request->filled('alamat_rw') ? 'RW ' . $request->input('alamat_rw') : null,
            $request->input('alamat_dusun'),
            $request->input('alamat_desa'),
            $request->input('alamat_kecamatan'),
            $request->input('alamat_kabupaten'),
            $request->input('alamat_provinsi'),
            $request->input('kode_pos'),
        ];
        $parts = array_filter(array_map('trim', $parts), fn($v) => $v !== '');
        return implode(', ', $parts);
    }

    protected function findOrCreateKamar(string $namaKamar): Kamar
    {
        $namaKamar = trim($namaKamar);

        $attributes = ['nama_kamar' => $namaKamar];

        if (Schema::hasTable('kamars') && !Schema::hasColumn('kamars', 'nama_kamar')) {
            if (Schema::hasColumn('kamars', 'nama')) {
                $attributes = ['nama' => $namaKamar];
            } elseif (Schema::hasColumn('kamars', 'kamar')) {
                $attributes = ['kamar' => $namaKamar];
            }
        }

        return Kamar::firstOrCreate(
            $attributes,
            ['wali_kamar' => null, 'deskripsi' => null]
        );
    }

    /**
     * Batasi hasil ke satu kelas pada satu jalur (pengajian atau sekolah).
     *
     * Santri yang foreign key-nya masih kosong tapi teksnya sama ikut serta,
     * supaya kelas hasil import lama atau yang diketik manual tidak hilang dari
     * filter.
     */
    protected function filterKelasJalur($query, string $id, string $kolomId, string $kolomTeks, ?string $nama = null): void
    {
        if ($id === '') {
            return;
        }

        if ($nama === null) {
            $kelas = Schema::hasTable('kelas_master') ? KelasMaster::find($id) : null;
            $nama = $kelas?->nama;

            if ($kelas === null) {
                // ID tidak dikenal (kelas dihapus) — jangan diam-diam menahan
                // seluruh daftar; abaikan filternya saja.
                return;
            }
        }

        $query->where(function ($q) use ($id, $kolomId, $kolomTeks, $nama) {
            $q->where($kolomId, $id)
              ->orWhere(function ($q2) use ($kolomId, $kolomTeks, $nama) {
                  $q2->whereNull($kolomId)->where($kolomTeks, $nama);
              });
        });
    }

    /**
     * Tambah kolom jenis_kelamin bila belum ada.
     *
     * Shared hosting tidak bisa menjalankan `php artisan migrate`, jadi
     * migrasi dijalankan sendiri lewat halaman ini memakai file migrasi
     * yang sama (idempotent: IF NOT EXISTS). Setelah kolom terbentuk,
     * fungsi ini hanya melakukan satu query INFORMATION_SCHEMA dan
     * langsung keluar.
     */
    protected function pastikanKolomJenisKelamin(): void
    {
        try {
            if (Schema::hasColumn('santris', 'jenis_kelamin')) {
                return;
            }

            $file = database_path('migrations/2026_10_10_000001_add_jenis_kelamin_to_santris_table.php');

            if (is_file($file)) {
                $migration = require $file;

                if (is_object($migration) && method_exists($migration, 'up')) {
                    $migration->up();
                }
            }

            SchemaCache::flush();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Parameter filter daftar santri yang dibawa bolak-balik ke form
     * tambah/ubah/hapus, supaya setelah kembali ke daftar santri
     * filter yang dipakai tidak reset ke "semua santri".
     *
     * `page` ikut dibawa supaya kembali ke halaman daftar yang
     * sama (mis. edit dari halaman 2 → kembali ke halaman 2,
     * bukan ke halaman 1).
     *
     * @return array<string, string>
     */
    protected function filterKembali(Request $request): array
    {
        return array_filter(
            $request->only([
                'page', 'search', 'kamar_id', 'filter', 'tahun_ajar', 'kelas_id',
                'jenis_kelas', 'kelas_pengajian_id', 'kelas_sekolah_id',
                'filter_jenis_kelamin',
            ]),
            fn ($v) => $v !== null && $v !== ''
        );
    }

    /**
     * Daftar kamar untuk dropdown filter: hanya kamar yang masih
     * ditempati santri. Kamar yang sudah tidak ada santrinya
     * disembunyikan otomatis dari dropdown.
     *
     * Kamar yang sedang dipilih tetap ditampilkan walau sudah kosong,
     * supaya filter yang aktif tidak menghilang dari dropdown.
     */
    protected function daftarKamarFilter(string $kamarId = ''): \Illuminate\Support\Collection
    {
        $idTerpakai = Santri::pluck('kamar_id')->unique()->filter()->all();

        $daftar = Kamar::whereIn('id', $idTerpakai)->get();

        if ($kamarId !== '' && !$daftar->contains('id', (int) $kamarId)) {
            $kamar = Kamar::find((int) $kamarId);

            if ($kamar) {
                $daftar = $daftar->push($kamar);
            }
        }

        return Kamar::urutRayon($daftar);
    }

    /**
     * Daftar kelas master yang masih dipakai santri untuk dropdown
     * filter. Kelas yang sudah tidak ada santrinya disembunyikan
     * otomatis — baik dipakai lewat foreign key maupun hanya
     * tersimpan sebagai teks (santri lama).
     *
     * Kelas yang sedang dipilih tetap ditampilkan walau sudah kosong,
     * supaya filter yang aktif tidak menghilang dari dropdown.
     */
    protected function daftarKelasFilter(string $tahunAjar, string $kelasPengajianId, string $kelasSekolahId): \Illuminate\Support\Collection
    {
        $semua = KelasMaster::tahunAjar($tahunAjar)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        if ($semua->isEmpty()) {
            return $semua;
        }

        $idPengajian = Schema::hasColumn('santris', 'kelas_pengajian_id')
            ? Santri::whereNotNull('kelas_pengajian_id')->pluck('kelas_pengajian_id')->map(fn ($v) => (int) $v)->unique()->all()
            : [];
        $idSekolah = Schema::hasColumn('santris', 'kelas_sekolah_id')
            ? Santri::whereNotNull('kelas_sekolah_id')->pluck('kelas_sekolah_id')->map(fn ($v) => (int) $v)->unique()->all()
            : [];
        $teksPengajian = Schema::hasColumn('santris', 'kelas_pengajian')
            ? Santri::whereNull('kelas_pengajian_id')->whereNotNull('kelas_pengajian')->pluck('kelas_pengajian')->unique()->all()
            : [];
        $teksSekolah = Schema::hasColumn('santris', 'kelas_sekolah')
            ? Santri::whereNull('kelas_sekolah_id')->whereNotNull('kelas_sekolah')->pluck('kelas_sekolah')->unique()->all()
            : [];

        // Kolom cermin lama (`kelas` + `kelas_master_id`): santri yang
        // dibuat sebelum kelas dua jalur, atau import format lama,
        // hanya punya nilai di sini. Ikut dihitung supaya kelasnya
        // tidak salah disembunyikan (sama seperti filter `kelas_id`).
        $idLama = Schema::hasColumn('santris', 'kelas_master_id')
            ? Santri::whereNotNull('kelas_master_id')->pluck('kelas_master_id')->map(fn ($v) => (int) $v)->unique()->all()
            : [];
        $teksLama = Schema::hasColumn('santris', 'kelas')
            ? Santri::whereNull('kelas_master_id')->where('kelas', '!=', '-')->whereNotNull('kelas')->pluck('kelas')->unique()->all()
            : [];

        $terpakai = $semua->filter(function ($k) use ($idPengajian, $idSekolah, $idLama, $teksPengajian, $teksSekolah, $teksLama) {
            $id = (int) $k->id;

            return in_array($id, $idPengajian, true)
                || in_array($id, $idSekolah, true)
                || in_array($id, $idLama, true)
                || in_array($k->nama, $teksPengajian, true)
                || in_array($k->nama, $teksSekolah, true)
                || in_array($k->nama, $teksLama, true);
        });

        // Kelas yang sedang dipilih tetap tampil walau sudah kosong.
        $terpilih = array_filter([
            $kelasPengajianId !== '' ? (int) $kelasPengajianId : null,
            $kelasSekolahId !== '' ? (int) $kelasSekolahId : null,
        ], fn ($v) => $v !== null);

        if ($terpilih !== []) {
            $ada = $terpakai->pluck('id')->map(fn ($v) => (int) $v)->all();
            $kurang = array_values(array_diff($terpilih, $ada));

            if ($kurang !== []) {
                $terpakai = $terpakai->merge(KelasMaster::whereIn('id', $kurang)->get())
                    ->sortBy(fn ($k) => [$k->tingkat, $k->nama])
                    ->values();
            }
        }

        return $terpakai;
    }

    // Tampilkan daftar santri
    public function index(Request $request)
    {
        $this->pastikanKolomJenisKelamin();

        // Cast ke string: middleware ConvertEmptyStringsToNull
        // mengubah nilai kosong (?kamar_id=) jadi null, dan
        // null lolos ke parameter bertipe string di bawah.
        $search = (string) $request->get('search', '');
        $kamarId = (string) $request->get('kamar_id', '');
        $filter = (string) $request->get('filter', '');
        $tahunAjar = (string) $request->get('tahun_ajar', '');
        $kelasId = (string) $request->get('kelas_id', '');
        $jenisKelas = (string) $request->get('jenis_kelas', '');
        $kelasPengajianId = (string) $request->get('kelas_pengajian_id', '');
        $kelasSekolahId = (string) $request->get('kelas_sekolah_id', '');
        $jenisKelamin = (string) $request->get('filter_jenis_kelamin', '');

        // Filter jenis kelamin hanya menerima dua nilai yang ada
        // di kolom; nilai lain diabaikan supaya tidak ada query
        // mencurigakan yang lolos.
        if (!in_array($jenisKelamin, ['Laki-laki', 'Perempuan'], true)) {
            $jenisKelamin = '';
        }

        // Dua jalur kelas punya foreign key masing-masing, jadi filter perlu
        // tahu kelas yang dipilih itu pengajian atau sekolah.
        if (!in_array($jenisKelas, [KelasMaster::PENGAJIAN, KelasMaster::FORMAL], true)) {
            $jenisKelas = '';
        }

        $query = Santri::with('kamar');

        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%');
        }

        if ($kamarId) {
            $query->where('kamar_id', $kamarId);
        }

        if ($jenisKelamin !== '' && Schema::hasColumn('santris', 'jenis_kelamin')) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        if ($filter === 'pengajar') {
            $query->where('is_pengajar', true);
        } elseif ($filter === 'santri') {
            $query->where('is_pengajar', false);
        }

        // Filter per jalur kelas (dipakai dropdown di halaman ini). Kalau kolom
        // ganda belum ada, jatuhkan ke kolom `kelas` teks yang lama.
        if (Schema::hasColumn('santris', 'kelas_pengajian_id')) {
            $this->filterKelasJalur($query, $kelasPengajianId, 'kelas_pengajian_id', 'kelas_pengajian');
            $this->filterKelasJalur($query, $kelasSekolahId, 'kelas_sekolah_id', 'kelas_sekolah');
        } else {
            if ($kelasPengajianId !== '' || $kelasSekolahId !== '') {
                $query->whereIn('kelas', array_values(array_filter([$kelasPengajianId, $kelasSekolahId])));
            }
        }

        // Filter generik `kelas_id` — dipakai tautan "Lihat Santri" di halaman
        // /kelas. `jenis_kelas` menentukan jalur; tanpa itu, cari di keduanya.
        if ($kelasId !== '' && Schema::hasTable('kelas_master')) {
            $kelasTerpilih = KelasMaster::find($kelasId);
            if ($kelasTerpilih) {
                $jalur = $jenisKelas === KelasMaster::PENGAJIAN ? ['kelas_pengajian_id', 'kelas_pengajian']
                    : ($jenisKelas === KelasMaster::FORMAL ? ['kelas_sekolah_id', 'kelas_sekolah'] : null);

                if ($jalur && Schema::hasColumn('santris', $jalur[0])) {
                    $this->filterKelasJalur($query, (string) $kelasTerpilih->id, $jalur[0], $jalur[1], $kelasTerpilih->nama);
                } elseif (Schema::hasColumn('santris', 'kelas_master_id')) {
                    $query->where(function ($q) use ($kelasTerpilih) {
                        $q->where('kelas_master_id', $kelasTerpilih->id)
                          ->orWhere(function ($q2) use ($kelasTerpilih) {
                              $q2->whereNull('kelas_master_id')->where('kelas', $kelasTerpilih->nama);
                          });
                    });
                } else {
                    $query->where('kelas', $kelasTerpilih->nama);
                }
            }
        }

        $santris = $query->orderBy('nama', 'asc')->paginate(15)->withQueryString();
        $daftarKamar = Kamar::urutRayon(Kamar::all());

        // Dropdown filter hanya menampilkan nilai yang masih dipakai
        // santri: kamar atau kelas yang sudah tidak ada santrinya
        // disembunyikan otomatis. $daftarKamar (semua kamar) dipakai
        // statistik Total Kamar; popup cetak rekap pakai daftar filter.
        $daftarKamarFilter = $this->daftarKamarFilter($kamarId);
        $kolomJenisKelamin = Schema::hasColumn('santris', 'jenis_kelamin');
        $filterKembali = $this->filterKembali($request);

        // Master kelas untuk filter. Tahun ajar default: yang sedang berjalan
        // atau terbaru yang tersedia.
        $daftarTahunAjar = [];
        $daftarKelas = collect();
        if (Schema::hasTable('kelas_master')) {
            $daftarTahunAjar = KelasMaster::daftarTahunAjar();
            if ($tahunAjar !== '' && !in_array($tahunAjar, $daftarTahunAjar, true)) {
                $tahunAjar = '';
            }
            if ($tahunAjar === '') {
                $tahunAjar = in_array(TahunAjar::sekarang(), $daftarTahunAjar, true)
                    ? TahunAjar::sekarang()
                    : ($daftarTahunAjar[0] ?? '');
            }
            if ($tahunAjar !== '') {
                $daftarKelas = $this->daftarKelasFilter($tahunAjar, $kelasPengajianId, $kelasSekolahId);
            }
        }

        $santriDetailData = $santris->map(function ($s) {
            // Kenaikan kelas sekolah (jalur formal): naik 1 tingkat tiap
            // tahun ajar, atau tamat kalau sudah di tingkat tertinggi.
            $kelasSekolah = $s->namaKelasSekolah();
            $naikSekolah = '-';
            if ($kelasSekolah !== '' && $kelasSekolah !== '-') {
                $parsed = TahunAjar::parseNamaKelas((string) $s->kelas_sekolah);
                $berikut = TahunAjar::namaKelasBerikutnya((string) $s->kelas_sekolah);
                if ($parsed['tingkat'] === null) {
                    $naikSekolah = $kelasSekolah . ' (tidak dapat ditentukan)';
                } elseif ($berikut === null) {
                    $naikSekolah = $kelasSekolah . ' → Tamat (tidak naik lagi)';
                } else {
                    $naikSekolah = $kelasSekolah . ' → ' . $berikut . ' (naik)';
                }
            }

            // Kenaikan kelas pengajian: kelas pengajian TIDAK naik tingkat
            // saat tahun ajar berganti, jadi namanya tetap.
            $kelasPengajian = $s->namaKelasPengajian();
            $naikPengajian = '-';
            if ($kelasPengajian !== '' && $kelasPengajian !== '-') {
                $naikPengajian = $kelasPengajian . ' → ' . $kelasPengajian . ' (tidak naik, pengajian tetap)';
            }

            // Status: Nonaktif -> "Non-aktif", lainnya -> "Aktif".
            $status = ($s->status === 'Nonaktif') ? 'Non-aktif' : 'Aktif';

            return [
                'id' => $s->id,
                'nama' => $s->nama,
                'kamar' => $s->kamar?->nama_kamar ?? '-',
                'kelas' => $s->kelas_gabungan,
                'kelas_pengajian' => $kelasPengajian,
                'kelas_sekolah' => $kelasSekolah,
                'naik_sekolah' => $naikSekolah,
                'naik_pengajian' => $naikPengajian,
                'tahun_ajar' => TahunAjar::sekarang(),
                'tahun_ajar_berikutnya' => TahunAjar::berikutnya(TahunAjar::sekarang()),
                'status' => $status,
                'dibuat' => $s->created_at ? $s->created_at->format('d M Y H:i') : '-',
                'jabatan' => $s->jabatan ?? '-',
                'is_pengajar' => $s->is_pengajar ? 'Ya' : 'Tidak',
                'no_hp_wali' => $s->no_hp_wali ?? '-',
                'no_whatsapp' => $s->no_whatsapp ?: '-',
                'alamat' => $s->alamat_lengkap ?: ($s->alamat_singkat ?: '-'),
                'latitude' => $s->latitude ?: '-',
                'longitude' => $s->longitude ?: '-',
            ];
        })->keyBy('id')->toArray();

        return view('santri.index', compact(
            'santris', 'daftarKamar', 'search', 'kamarId', 'filter', 'santriDetailData',
            'daftarTahunAjar', 'daftarKelas', 'tahunAjar', 'kelasId', 'jenisKelas',
            'kelasPengajianId', 'kelasSekolahId', 'jenisKelamin', 'daftarKamarFilter',
            'kolomJenisKelamin', 'filterKembali'
        ));
    }

    /**
     * Jaga agar kolom teks `kelas` dan `kelas_master_id` selalu konsisten:
     * saat form/import) mengisi kelas yang belum ada di master, kelas
     * itu otomatis didaftarkan pada tahun ajar berjalan.
     *
     * Sekarang ada dua jalur kelas, jadi setiap jalur dicocokkan ke master
     * dengan jenis yang eksplisit — dropdown di form sudah memisahkan
     * pengajian dan sekolah, jadi jenis tidak boleh ditebak ulang dari nama.
     */
    protected function sinkronKelasMaster(?string $namaKelas, ?string $jenis = null): ?KelasMaster
    {
        $namaKelas = trim((string) $namaKelas);

        if ($namaKelas === '' || $namaKelas === '-' || !Schema::hasTable('kelas_master')) {
            return null;
        }

        if (!Schema::hasColumn('santris', 'kelas_master_id')) {
            return null;
        }

        return KelasMaster::findOrBuat($namaKelas, null, $jenis);
    }

    /**
     * Rakit data kelas dari form: dua jalur kelas + kolom cermin lama.
     *
     * Kolom `kelas` (NOT NULL) dan `kelas_master_id` tidak dihapus demi
     * kompatibilitas, tapi isinya dibuat mengikuti kelas sekolah; kalau kelas
     * sekolah kosong, baru pakai kelas pengajian. Dengan begitu halaman lama,
     * cetak, dan ekspor lama tetap menampilkan sesuatu yang benar.
     */
    protected function dataKelasDariForm(Request $request): array
    {
        $pengajian = trim((string) $request->input('kelas_pengajian', ''));
        $sekolah = trim((string) $request->input('kelas_sekolah', ''));

        // Form lama (dan import file lama) hanya punya satu kolom `kelas`.
        // Nadaikan ke jalur yang cocok berdasarkan jenis master yang ada.
        $lama = trim((string) $request->input('kelas', ''));
        if ($pengajian === '' && $sekolah === '' && $lama !== '' && $lama !== '-') {
            $master = $this->sinkronKelasMaster($lama);
            if ($master && $master->isPengajian()) {
                $pengajian = $lama;
            } else {
                $sekolah = $lama;
            }
        }

        $data = [
            'kelas_pengajian' => $pengajian !== '' ? $pengajian : null,
            'kelas_sekolah' => $sekolah !== '' ? $sekolah : null,
        ];

        if (!Schema::hasColumn('santris', 'kelas_pengajian')) {
            return $data;
        }

        $masterPengajian = $pengajian !== ''
            ? $this->sinkronKelasMaster($pengajian, TahunAjar::JENIS_PENGAJIAN)
            : null;
        $masterSekolah = $sekolah !== ''
            ? $this->sinkronKelasMaster($sekolah, TahunAjar::JENIS_FORMAL)
            : null;

        $data['kelas_pengajian_id'] = $masterPengajian?->id;
        $data['kelas_sekolah_id'] = $masterSekolah?->id;

        // Cermin kolom lama: pakai sekolah dulu, baru pengajian.
        $data['kelas'] = $sekolah !== '' ? $sekolah : ($pengajian !== '' ? $pengajian : '-');
        $data['kelas_master_id'] = $masterSekolah?->id ?? $masterPengajian?->id;

        return $data;
    }

    // Form create santri
    public function create()
    {
        $this->pastikanKolomJenisKelamin();

        $daftarKamar = Kamar::urutRayon(Kamar::all());
        $daftarKelas = $this->daftarKelasForm();
        $filterKembali = $this->filterKembali(request());
        $kolomJenisKelamin = Schema::hasColumn('santris', 'jenis_kelamin');

        return view('santri.create', compact(
            'daftarKamar', 'daftarKelas', 'filterKembali', 'kolomJenisKelamin'
        ));
    }

    /**
     * Daftar kelas untuk dropdown form Santri, dikelompokkan per tahun ajar
     * lalu dipisah pengajian vs sekolah.
     *
     * Bila tabel kelas_master atau kolom kelas ganda belum ada (mis. migrasi
     * belum sempat jalan), dikembalikan array kosong — blade otomatis berubah
     * jadi input teks bebas supaya admin tetap bisa mengisi.
     */
    protected function daftarKelasForm(?string $tahunAjar = null): array
    {
        if (!Schema::hasTable('kelas_master') || !Schema::hasColumn('santris', 'kelas_pengajian')) {
            return [];
        }

        return KelasMaster::untukDropdown($tahunAjar);
    }

    // Store santri baru
    public function store(Request $request)
    {
        $rules = [
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'kamar' => 'required|string|max:100',
            'kelas_pengajian' => 'nullable|string|max:100',
            'kelas_sekolah' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'no_hp_wali' => 'nullable|string|max:20',
        ];
        $rules = array_merge($rules, $this->getAddressRules());

        $validated = $request->validate($rules, [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'kamar.required' => 'Kamar/Asrama wajib diisi.',
            'jabatan.max' => 'Jabatan maksimal 100 karakter.',
            'no_hp_wali.max' => 'Nomor HP wali maksimal 20 karakter.',
            'no_whatsapp.max' => 'Nomor WhatsApp maksimal 20 karakter.',
            'foto_rumah_consent.in' => 'Persetujuan foto rumah tidak valid.',
        ]);

        $kamar = $this->findOrCreateKamar($validated['kamar']);

        $santriData = [
            'nama' => $validated['nama'],
            'kamar_id' => $kamar->id,
            'jabatan' => $validated['jabatan'] ?? null,
            'is_pengajar' => $request->has('is_pengajar'),
            'no_hp_wali' => $validated['no_hp_wali'] ?? null,
            'nis' => uniqid('S'),
        ];

        if (Schema::hasColumn('santris', 'jenis_kelamin')) {
            $santriData['jenis_kelamin'] = ($validated['jenis_kelamin'] ?? '') ?: null;
        }

        $santriData = array_merge($santriData, $this->dataKelasDariForm($request));
        $santriData = array_merge($santriData, $this->getAddressData($request));

        Santri::create($santriData);

        return redirect()->route('santri.create', $this->filterKembali($request))
                       ->with('success', 'Data Santri berhasil disimpan.')
                       ->with('success_judul', 'Sudah Disimpan');
    }

    // Form edit santri
    public function edit(Santri $santri)
    {
        $this->pastikanKolomJenisKelamin();

        $daftarKamar = Kamar::urutRayon(Kamar::all());
        $daftarKelas = $this->daftarKelasForm();
        $filterKembali = $this->filterKembali(request());
        $kolomJenisKelamin = Schema::hasColumn('santris', 'jenis_kelamin');

        return view('santri.edit', compact(
            'santri', 'daftarKamar', 'daftarKelas', 'filterKembali', 'kolomJenisKelamin'
        ));
    }

    // Update santri
    public function update(Request $request, Santri $santri)
    {
        $rules = [
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'kamar' => 'required|string|max:100',
            'kelas_pengajian' => 'nullable|string|max:100',
            'kelas_sekolah' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'no_hp_wali' => 'nullable|string|max:20',
        ];
        $rules = array_merge($rules, $this->getAddressRules());

        $validated = $request->validate($rules, [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'kamar.required' => 'Kamar/Asrama wajib diisi.',
            'jabatan.max' => 'Jabatan maksimal 100 karakter.',
            'no_hp_wali.max' => 'Nomor HP wali maksimal 20 karakter.',
            'no_whatsapp.max' => 'Nomor WhatsApp maksimal 20 karakter.',
            'foto_rumah_consent.in' => 'Persetujuan foto rumah tidak valid.',
        ]);

        $kamar = $this->findOrCreateKamar($validated['kamar']);

        $santriData = [
            'nama' => $validated['nama'],
            'kamar_id' => $kamar->id,
            'jabatan' => $validated['jabatan'] ?? null,
            'is_pengajar' => $request->has('is_pengajar'),
            'no_hp_wali' => $validated['no_hp_wali'] ?? null,
        ];

        if (Schema::hasColumn('santris', 'jenis_kelamin')) {
            $santriData['jenis_kelamin'] = ($validated['jenis_kelamin'] ?? '') ?: null;
        }

        $santriData = array_merge($santriData, $this->dataKelasDariForm($request));
        $santriData = array_merge($santriData, $this->getAddressData($request));

        $santri->update($santriData);

        return redirect()->route('santri.edit', array_merge(['santri' => $santri->id], $this->filterKembali($request)))
                       ->with('success', 'Data Santri berhasil diperbaharui.')
                       ->with('success_judul', 'Sudah Diperbaharui');
    }

    // Delete santri
    public function destroy(Santri $santri, Request $request)
    {
        $santri->delete();

        // Bawa kembali parameter filter supaya daftar santri tidak
        // reset ke "semua santri" setelah satu baris dihapus.
        return redirect()->route('santri.index', $this->filterKembali($request))
                       ->with('success', '✅ Santri berhasil dihapus!');
    }

    // Toggle status santri
    public function toggleStatus(Santri $santri)
    {
        // Status otomatis set ke Aktif saat create, jadi feature ini bisa dihapus
        // Atau bisa digunakan untuk fitur aktif/nonaktif di masa depan
        return back();
    }

    // Form import santri dari CSV
    public function importForm()
    {
        $daftarKamar = Kamar::urutRayon(Kamar::all());
        return view('santri.import', compact('daftarKamar'));
    }

    /**
     * Daftar kolom template import; jadi satu sumber data untuk CSV maupun Excel.
     *
     * Kolom kelas dipecah jadi dua karena di pesantren ada dua jalur yang
     * berjalan bersamaan: kelas pengajian (tidak naik otomatis, tidak semua
     * orangnya naik) dan kelas sekolah (naik satu tingkat tiap tahun).
     */
    protected function kolomTemplateImport(): array
    {
        return [
            'Nama Santri', 'Rayon/Kamar', 'Kelas Pengajian', 'Kelas Sekolah', 'Jabatan',
            'No. HP Wali', 'No. WhatsApp', 'Provinsi', 'Kabupaten/Kota',
            'Kecamatan', 'Desa/Kelurahan', 'Dusun/Kampung', 'RT', 'RW',
            'Jalan', 'No. Rumah', 'Kode Pos', 'Jenis Kelamin',
        ];
    }

    // Lebar kolom template (sesuai urutan kolomTemplateImport()).
    protected function lebarKolomTemplateImport(): array
    {
        return [24, 16, 16, 14, 14, 16, 16, 16, 18, 16, 20, 16, 7, 7, 24, 12, 12, 14];
    }

    // Baris judul pada template (boleh dihapus pengguna, import tetap jalan).
    protected function judulTemplateImport(): string
    {
        return 'DATA SANTRI - FORMULIR IMPORT SANTRI.ID';
    }

    protected function keteranganTemplateImport(): string
    {
        return 'Isi data pada baris di bawah. Kolom Nama Santri dan Rayon/Kamar wajib diisi, kolom lain boleh dikosongkan. '
            . 'Kelas Pengajian diisi untuk kelas majelis (mis. 1, Takhosus I) dan tidak naik otomatis. '
            . 'Kelas Sekolah diisi untuk kelas formal (mis. 7 SMP) yang naik tiap tahun. Rayon baru dibuat otomatis.';
    }

    /**
     * Daftar lengkap nama kolom beserta label yang dipakai saat membaca file.
     *
     * Dipakai importStore() untuk memetakan baris ke nama kolom, bukan
     * berdasarkan posisinya — jadi file lama yang hanya punya satu kolom `Kelas`
     * tetap bisa diimport, dan urutan kolom di template boleh diubah tanpa
     * merusak import.
     *
     * @return array<string, array<int, string>>  nama field => daftar alias
     */
    protected function aliasKolomImport(): array
    {
        return [
            'nama' => [0, 'nama', 'namasantri', 'namasantri_lengkap'],
            'kamar' => [1, 'rayon', 'rayonkamar', 'rayon_kamar', 'kamar', 'kamarasrama'],
            'kelas_pengajian' => [2, 'kelaspengajian', 'kelas_pengajian', 'pengajian', 'majelis'],
            'kelas_sekolah' => [3, 'kelassekolah', 'kelas_sekolah', 'sekolah', 'formal', 'jenjangpendidikan', 'jenjang'],
            'kelas' => [4, 'kelas'],
            'jabatan' => [5, 'jabatan'],
            'no_hp_wali' => [6, 'nohpwali', 'no_hp_wali', 'hp_wali', 'nohp'],
            'no_whatsapp' => [7, 'nowhatsapp', 'no_whatsapp', 'whatsapp', 'wa'],
            'alamat_provinsi' => [8, 'provinsi'],
            'alamat_kabupaten' => [9, 'kabupatenkota', 'kabupaten_kota', 'kabupaten', 'kota'],
            'alamat_kecamatan' => [10, 'kecamatan'],
            'alamat_desa' => [11, 'desakelurahan', 'desa_kelurahan', 'desa'],
            'alamat_dusun' => [12, 'dusunkampung', 'dusun_kampung', 'dusun', 'kampung'],
            'alamat_rt' => [13, 'rt'],
            'alamat_rw' => [14, 'rw'],
            'alamat_jalan' => [15, 'jalan', 'alamat'],
            'alamat_nomor_rumah' => [16, 'norumah', 'no_rumah', 'nomor_rumah', 'norumah'],
            'kode_pos' => [17, 'kodepos', 'kode_pos'],
            'jenis_kelamin' => [18, 'jeniskelamin', 'jenis_kelamin', 'kelamin', 'jk', 'gender'],
        ];
    }

    /**
     * Normalisasi nilai jenis kelamin dari file import.
     *
     * Menerima variasi umum — Laki-laki/Laki/Pria/Cowok/Lelaki,
     * Perempuan/Wanita/Cewek, singkatan L/P — dan mengembalikan
     * nilai baku kolom. Nilai kosong atau tidak dikenal jadi null
     * (kolom jenis kelamin opsional, baris tetap diimport).
     */
    protected function normalisasiJenisKelamin(string $nilai): ?string
    {
        $kunci = strtolower(preg_replace('/[^a-z]/i', '', trim($nilai)));

        return match ($kunci) {
            'l', 'laki', 'lakilaki', 'pria', 'cowok', 'lelaki' => 'Laki-laki',
            'p', 'perempuan', 'wanita', 'cewek' => 'Perempuan',
            default => null,
        };
    }

    /**
     * Peta indeks kolom -> nama field, dibaca dari baris judul kolom.
     *
     * Alias yang dikenali ada di aliasKolomImport(). Baris judul kolom dicari di
     * enam baris pertama supaya baris judul dokumen dan baris keterangan yang
     * boleh dihapus pengguna tidak menghalangi. Kalau tidak ketemu, jatuh ke
     * urutan baku kolomTemplateImport().
     *
     * $baris harus berupa DAFTAR BARIS (array of array), bukan satu baris —
     * satu baris pun boleh, karena pemanggil cukup mengoper `[$barisJudul]`.
     *
     * @param array<int, mixed> $baris
     * @return array<string, int>  nama field => indeks kolom
     */
    protected function petaKolomImport(array $baris): array
    {
        $alias = $this->aliasKolomImport();

        foreach (array_slice($baris, 0, 6, true) as $barisSatu) {
            // Baris dari CSV tanpa header bisa berupa string (satu kolom saja).
            // Yang bisa dipetakan hanya baris yang benar-benar berupa daftar.
            if (!is_array($barisSatu)) {
                continue;
            }

            $peta = [];

            foreach ($barisSatu as $indeks => $judul) {
                $kunci = strtolower(preg_replace('/[^a-z0-9]+/i', '', trim((string) $judul)));
                if ($kunci === '') {
                    continue;
                }

                foreach ($alias as $field => $daftar) {
                    // Kolom pertama yang cocok menang, dan daftar alias diurutkan
                    // dari yang paling spesifik. Jadi kolom "Kelas Pengajian"
                    // tidak ikut tertimpa kolom umum "Kelas".
                    if (in_array($kunci, $daftar, true)) {
                        $peta[$field] ??= (int) $indeks;
                    }
                }
            }

            // Header dianggap dikenali kalau kolom nama dan kamar ketemu.
            if (isset($peta['nama'], $peta['kamar'])) {
                return $peta;
            }
        }

        // Tidak ada baris judul kolom: pakai urutan baku template.
        // Jenis Kelamin sengaja di posisi terakhir supaya file
        // tanpa header (urutan baku lama) tetap terbaca dengan
        // posisi yang sama seperti sebelumnya.
        $peta = [];
        $urutan = ['nama', 'kamar', 'kelas_pengajian', 'kelas_sekolah', 'jabatan',
            'no_hp_wali', 'no_whatsapp', 'alamat_provinsi', 'alamat_kabupaten',
            'alamat_kecamatan', 'alamat_desa', 'alamat_dusun', 'alamat_rt',
            'alamat_rw', 'alamat_jalan', 'alamat_nomor_rumah', 'kode_pos',
            'jenis_kelamin'];

        foreach ($urutan as $i => $field) {
            $peta[$field] = $i;
        }

        // Versi lama hanya punya satu kolom "Kelas" di posisi yang sekarang
        // dipakai "Kelas Pengajian", jadi file lama perlu dipetakan manual ke
        // jalur yang benar.
        $peta['kelas_legacy'] = 2;

        return $peta;
    }

    /**
     * Unduh template import. Default .xlsx yang sudah bergaris dan siap diisi,
     * bisa langsung di-upload kembali tanpa diubah. ?format=csv untuk versi polos.
     */
    public function importTemplate(Request $request)
    {
        $format = strtolower((string) $request->get('format', 'xlsx')) === 'csv' ? 'csv' : 'xlsx';
        $header = $this->kolomTemplateImport();
        $judul = $this->judulTemplateImport();
        $keterangan = $this->keteranganTemplateImport();
        $namaFile = 'template_import_santri_' . now()->format('Y-m-d');

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($header, $judul, $keterangan) {
                $out = fopen('php://output', 'w');
                // BOM UTF-8 supaya Excel tidak mengubah karakter Indonesia jadi kode.
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, [$judul]);
                fputcsv($out, [$keterangan]);
                fputcsv($out, $header);
                for ($i = 0; $i < 30; $i++) {
                    fputcsv($out, array_fill(0, count($header), ''));
                }
                fclose($out);
            }, $namaFile . '.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache',
            ]);
        }

        $path = storage_path('app/' . $namaFile . '.xlsx');
        $this->tulisXlsxTemplate($path, $header, $this->lebarKolomTemplateImport(), 30, $judul, $keterangan);

        return response()->download($path, $namaFile . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** Tulis workbook .xlsx template: judul, seluruh sel bergaris, siap diisi. */
    protected function tulisXlsxTemplate(string $path, array $header, array $lebar, int $jumlahBaris, string $judul = '', string $keterangan = ''): void
    {
        $xml = function ($teks): string {
            return htmlspecialchars((string) $teks, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        };

        $hurufKolom = function (int $index): string {
            $huruf = '';
            while ($index > 0) {
                $sisa = ($index - 1) % 26;
                $huruf = chr(65 + $sisa) . $huruf;
                $index = intdiv($index - 1, 26);
            }

            return $huruf;
        };

        $jumlahKolom = count($header);
        $kolomTerakhir = $hurufKolom($jumlahKolom);
        $sheetRows = '';
        $barisHeader = ($judul !== '' || $keterangan !== '') ? 3 : 1;

        // Baris judul (merge sel) + baris keterangan
        if ($judul !== '') {
            $sheetRows .= '<row r="1" ht="30" customHeight="1">'
                . '<c r="A1" s="3" t="inlineStr"><is><t xml:space="preserve">' . $xml($judul) . '</t></is></c>'
                . '</row>';
        }
        if ($keterangan !== '') {
            $sheetRows .= '<row r="2" ht="20" customHeight="1">'
                . '<c r="A2" s="4" t="inlineStr"><is><t xml:space="preserve">' . $xml($keterangan) . '</t></is></c>'
                . '</row>';
        }

        // Baris judul kolom
        $cells = '';
        foreach ($header as $c => $namaKolom) {
            $ref = $hurufKolom($c + 1) . $barisHeader;
            $cells .= '<c r="' . $ref . '" s="1" t="inlineStr"><is><t xml:space="preserve">'
                . $xml($namaKolom) . '</t></is></c>';
        }
        $sheetRows .= '<row r="' . $barisHeader . '" ht="26" customHeight="1">' . $cells . '</row>';

        // Baris kosong siap isi (sel tetap ditulis agar garisnya terlihat)
        for ($r = 1; $r <= $jumlahBaris; $r++) {
            $cells = '';
            for ($c = 0; $c < $jumlahKolom; $c++) {
                $cells .= '<c r="' . $hurufKolom($c + 1) . ($barisHeader + $r) . '" s="2"/>';
            }
            $sheetRows .= '<row r="' . ($barisHeader + $r) . '" ht="18" customHeight="1">' . $cells . '</row>';
        }

        $merge = '';
        if ($judul !== '') {
            $merge .= '<mergeCell ref="A1:' . $kolomTerakhir . '1"/>';
        }
        if ($keterangan !== '') {
            $merge .= '<mergeCell ref="A2:' . $kolomTerakhir . '2"/>';
        }
        $merge = $merge !== '' ? '<mergeCells count="' . substr_count($merge, '<mergeCell ') . '">' . $merge . '</mergeCells>' : '';

        $cols = '';
        foreach ($lebar as $c => $w) {
            $cols .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . $w . '" customWidth="1"/>';
        }

        $parts = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
                . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="Data Santri" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                . '</Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="4">'
                . '<font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>'
                . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
                . '<font><b/><sz val="14"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
                . '<font><i/><sz val="10"/><color rgb="FF475569"/><name val="Calibri"/></font>'
                . '</fonts>'
                . '<fills count="4"><fill><patternFill patternType="none"/></fill>'
                . '<fill><patternFill patternType="gray125"/></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FF10B981"/><bgColor indexed="64"/></patternFill></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/><bgColor indexed="64"/></patternFill></fill>'
                . '</fills>'
                . '<borders count="3">'
                . '<border><left/><right/><top/><bottom/><diagonal/></border>'
                . '<border>'
                . '<left style="thin"><color rgb="FF94A3B8"/></left>'
                . '<right style="thin"><color rgb="FF94A3B8"/></right>'
                . '<top style="thin"><color rgb="FF94A3B8"/></top>'
                . '<bottom style="thin"><color rgb="FF94A3B8"/></bottom>'
                . '<diagonal/></border>'
                . '<border>'
                . '<left style="medium"><color rgb="FF10B981"/></left>'
                . '<right style="medium"><color rgb="FF10B981"/></right>'
                . '<top style="medium"><color rgb="FF10B981"/></top>'
                . '<bottom style="medium"><color rgb="FF10B981"/></bottom>'
                . '<diagonal/></border>'
                . '</borders>'
                . '<cellStyleXfs count="1"><xf/></cellStyleXfs>'
                . '<cellXfs count="5">'
                . '<xf xfId="0"/>'
                . '<xf xfId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
                . '<alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
                . '<xf xfId="0" borderId="1" applyBorder="1" applyAlignment="1">'
                . '<alignment vertical="center"/></xf>'
                . '<xf xfId="0" fontId="2" fillId="2" borderId="2" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
                . '<alignment horizontal="center" vertical="center"/></xf>'
                . '<xf xfId="0" fontId="3" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
                . '<alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
                . '</cellXfs>'
                . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
                . '</styleSheet>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<sheetViews><sheetView workbookViewId="0">'
                . '<pane ySplit="' . $barisHeader . '" topLeftCell="A' . ($barisHeader + 1) . '" activePane="bottomLeft" state="frozen"/>'
                . '</sheetView></sheetViews>'
                . '<cols>' . $cols . '</cols>'
                . '<sheetData>' . $sheetRows . '</sheetData>'
                . $merge
                . '</worksheet>',
        ];

        if (file_exists($path)) {
            @unlink($path);
        }

        $this->tulisZip($path, $parts);
    }

    /** Baca file CSV (pemisah koma atau titik-koma) menjadi array baris. */
    protected function bacaCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $firstLine = fgets($handle);
        $delimiter = substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',') ? ';' : ',';
        rewind($handle);

        $rows = [];
        while (($raw = fgetcsv($handle, 1000, $delimiter)) !== false) {
            if (count($raw) < 1 || trim(implode('', $raw)) === '') {
                continue;
            }
            $rows[] = array_map(fn ($v) => trim((string) $v), $raw);
        }
        fclose($handle);

        return $rows;
    }

    /** Baca sel .xlsx (sheet pertama) menjadi array baris tanpa dependensi ZipArchive. */
    protected function bacaXlsx(string $path): array
    {
        $parts = $this->bacaZip($path);
        if (empty($parts)) {
            return [];
        }

        $bersama = [];
        if (isset($parts['xl/sharedStrings.xml'])) {
            $xml = @simplexml_load_string($parts['xl/sharedStrings.xml']);
            if ($xml !== false) {
                foreach ($xml->si as $si) {
                    $bersama[] = $this->teksXml($si);
                }
            }
        }

        $sheet = null;
        foreach (array_keys($parts) as $nama) {
            if (strpos($nama, 'worksheets/sheet') !== false && substr($nama, -4) === '.xml') {
                $sheet = $parts[$nama];
                break;
            }
        }
        if ($sheet === null) {
            return [];
        }

        $xml = @simplexml_load_string($sheet);
        if ($xml === false || !isset($xml->sheetData)) {
            return [];
        }

        $baris = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $huruf = preg_replace('/[0-9]/', '', $ref);
                $kolom = 0;
                $panjang = strlen($huruf);
                for ($i = 0; $i < $panjang; $i++) {
                    $kolom = $kolom * 26 + (ord(strtoupper($huruf[$i])) - 64);
                }
                $kolom--;

                $tipe = (string) $c['t'];
                if ($tipe === 'inlineStr') {
                    $nilai = isset($c->is) ? $this->teksXml($c->is) : '';
                } elseif ($tipe === 's') {
                    $nilai = $bersama[(int) $c->v] ?? '';
                } else {
                    $nilai = isset($c->v) ? (string) $c->v : '';
                }
                $cells[$kolom] = $nilai;
            }
            ksort($cells);
            $baris[] = array_values($cells);
        }

        return $baris;
    }

    /** Ambil seluruh teks turunan sebuah node SimpleXML (menangani <t> yang terpecah). */
    protected function teksXml($node): string
    {
        $teks = '';
        if (isset($node->t)) {
            foreach ($node->t as $t) {
                $teks .= (string) $t;
            }

            return $teks;
        }

        return trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $node)));
    }

    /** Baca isi arsip zip (mendukung stored & deflate) tanpa ZipArchive. */
    protected function bacaZip(string $path): array
    {
        $data = @file_get_contents($path);
        if ($data === false || strlen($data) < 22) {
            return [];
        }

        $eocd = strrpos($data, "\x50\x4b\x05\x06");
        if ($eocd === false) {
            return [];
        }

        $jumlah = unpack('v', substr($data, $eocd + 10, 2))[1] ?? 0;
        $offset = unpack('V', substr($data, $eocd + 16, 4))[1] ?? 0;

        $hasil = [];
        for ($i = 0; $i < $jumlah; $i++) {
            if (substr($data, $offset, 4) !== "\x50\x4b\x01\x02") {
                break;
            }
            $metode = unpack('v', substr($data, $offset + 10, 2))[1] ?? 0;
            $csize = unpack('V', substr($data, $offset + 20, 4))[1] ?? 0;
            $namaLen = unpack('v', substr($data, $offset + 28, 2))[1] ?? 0;
            $extraLen = unpack('v', substr($data, $offset + 30, 2))[1] ?? 0;
            $komentarLen = unpack('v', substr($data, $offset + 32, 2))[1] ?? 0;
            $local = unpack('V', substr($data, $offset + 42, 4))[1] ?? 0;
            $nama = substr($data, $offset + 46, $namaLen);
            $offset += 46 + $namaLen + $extraLen + $komentarLen;

            $lNamaLen = unpack('v', substr($data, $local + 26, 2))[1] ?? 0;
            $lExtraLen = unpack('v', substr($data, $local + 28, 2))[1] ?? 0;
            $mulai = $local + 30 + $lNamaLen + $lExtraLen;
            $isi = substr($data, $mulai, $csize);

            if ($metode === 8) {
                $isi = @gzinflate($isi);
            }
            if ($isi === false) {
                continue;
            }

            $hasil[$nama] = $isi;
        }

        return $hasil;
    }

    /**
     * Buang baris judul (mis. "DATA SANTRI - FORMULIR ...") dan baris keterangan,
     * lalu buang juga baris judul kolom. Kalau baris judul kolom tidak ditemukan,
     * semua baris dianggap data (format lama tanpa header).
     *
     * @return array{0: array<int, array<int, string>>, 1: array<string, int>|null}
     *         baris data + peta kolom (null = pakai urutan baku)
     */
    protected function lewatiBarisJudul(array $rows): array
    {
        foreach (array_slice($rows, 0, 6, true) as $index => $row) {
            $kolom0 = strtolower(trim((string) ($row[0] ?? '')));
            $kolom1 = strtolower(trim((string) ($row[1] ?? '')));

            if (str_contains($kolom0, 'nama') && (str_contains($kolom1, 'rayon') || str_contains($kolom1, 'kamar'))) {
                return [array_values(array_slice($rows, $index + 1)), $this->petaKolomImport([$row])];
            }
        }

        // Tidak ada baris judul kolom: file lama tanpa header, pakai posisi baku.
        return [$rows, null];
    }

    // Proses import data santri dari file CSV atau XLSX
    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:8192',
        ]);

        $berkas = $request->file('file');
        $ekstensi = strtolower((string) ($berkas->getClientOriginalExtension() ?: $berkas->extension()));

        if (in_array($ekstensi, ['xlsx', 'xlsm'], true)) {
            $rows = $this->bacaXlsx($berkas->getRealPath());
            $rows = array_map(fn ($r) => array_map(fn ($v) => trim((string) $v), $r), $rows);
            $rows = array_values(array_filter($rows, fn ($r) => trim(implode('', $r)) !== ''));
        } else {
            $rows = $this->bacaCsv($berkas->getRealPath());
        }

        if (empty($rows)) {
            return back()->with('error', 'File kosong atau tidak dapat dibaca.');
        }

        // Buang baris judul/keterangan di atas baris judul kolom, dan ambil
        // peta kolom dari baris judul itu sekaligus.
        [$rows, $peta] = $this->lewatiBarisJudul($rows);
        $peta = $peta ?: $this->petaKolomImport([]);

        $berhasil = 0; $duplikat = 0; $gagal = 0;

        foreach ($rows as $row) {
            $ambil = function (string $field) use ($row, $peta) {
                $indeks = $peta[$field] ?? null;

                return $indeks === null ? '' : trim((string) ($row[$indeks] ?? ''));
            };

            $nama = $ambil('nama');
            $kamarNama = $ambil('kamar');
            $jabatan = $ambil('jabatan');
            $jenisKelamin = $this->normalisasiJenisKelamin($ambil('jenis_kelamin'));

            $no_hp_wali = $ambil('no_hp_wali');
            $no_whatsapp = $ambil('no_whatsapp');
            $alamat = [
                'alamat_provinsi' => $ambil('alamat_provinsi'),
                'alamat_kabupaten' => $ambil('alamat_kabupaten'),
                'alamat_kecamatan' => $ambil('alamat_kecamatan'),
                'alamat_desa' => $ambil('alamat_desa'),
                'alamat_dusun' => $ambil('alamat_dusun'),
                'alamat_rt' => $ambil('alamat_rt'),
                'alamat_rw' => $ambil('alamat_rw'),
                'alamat_jalan' => $ambil('alamat_jalan'),
                'alamat_nomor_rumah' => $ambil('alamat_nomor_rumah'),
                'kode_pos' => $ambil('kode_pos'),
            ];

            $pengajian = $ambil('kelas_pengajian');
            $sekolah = $ambil('kelas_sekolah');

            // File lama / file tanpa baris judul kolom hanya punya satu kolom
            // "Kelas". Ditentukan ke jalur mana lewat jenis master yang namanya
            // cocok, supaya "Takhosus I" jadi pengajian dan "7 SMP" jadi sekolah.
            $lama = $ambil('kelas');
            if ($pengajian === '' && $sekolah === '' && $lama !== '') {
                $master = $this->sinkronKelasMaster($lama);
                if ($master && $master->isPengajian()) {
                    $pengajian = $lama;
                } else {
                    $sekolah = $lama;
                }
            }

            if ($nama === '' || $kamarNama === '') { $gagal++; continue; }

            $exists = Santri::where('nama', $nama)
                ->whereHas('kamar', fn ($q) => $q->where('nama_kamar', $kamarNama))
                ->exists();
            if ($exists) { $duplikat++; continue; }

            try {
                $kamar = $this->findOrCreateKamar($kamarNama);
                $createData = [
                    'nama' => $nama,
                    'kamar_id' => $kamar->id,
                    'jabatan' => $jabatan !== '' ? $jabatan : null,
                    'no_hp_wali' => $no_hp_wali !== '' ? $no_hp_wali : null,
                    'nis' => uniqid('S'),
                ];

                if ($jenisKelamin !== null && Schema::hasColumn('santris', 'jenis_kelamin')) {
                    $createData['jenis_kelamin'] = $jenisKelamin;
                }

                // Kelas ganda disinkronkan lewat Request palsu supaya form dan
                // import memakai aturan persis yang sama (termasuk master kelas
                // yang dibuat otomatis bila belum terdaftar).
                $createData += $this->dataKelasDariForm(new Request([
                    'kelas_pengajian' => $pengajian,
                    'kelas_sekolah' => $sekolah,
                ]));

                foreach ($alamat as $key => $value) {
                    if ($value !== '' && Schema::hasColumn('santris', $key)) {
                        $createData[$key] = $value;
                    }
                }
                if (Schema::hasColumn('santris', 'no_whatsapp') && $no_whatsapp !== '') {
                    $createData['no_whatsapp'] = $no_whatsapp;
                }
                if (Schema::hasColumn('santris', 'alamat_lengkap')) {
                    $createData['alamat_lengkap'] = implode(', ', array_filter($alamat, fn($v) => $v !== ''));
                }
                Santri::create($createData);
                $berhasil++;
            } catch (\Throwable $e) {
                $gagal++;
            }
        }

        $pesan = "Import selesai: {$berhasil} santri ditambahkan";
        if ($duplikat > 0) $pesan .= ", {$duplikat} duplikat dilewati";
        if ($gagal > 0) $pesan .= ", {$gagal} baris gagal";

        return redirect()->route('santri.index')->with('success', $pesan . '.');
    }

    public function exportFdf(Request $request)
    {
        $search = $request->get('search', '');
        $kamarId = $request->get('kamar_id', '');
        $filter = $request->get('filter', '');

        $query = Santri::with('kamar');

        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%');
        }
        if ($kamarId) {
            $query->where('kamar_id', $kamarId);
        }
        if ($filter === 'pengajar') {
            $query->where('is_pengajar', true);
        } elseif ($filter === 'santri') {
            $query->where('is_pengajar', false);
        }

        $jenisKelamin = $this->filterJenisKelamin($request);
        if ($jenisKelamin !== '' && Schema::hasColumn('santris', 'jenis_kelamin')) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        $santris = $query->orderBy('nama', 'asc')->get();

        $fields = [];
        foreach ($santris as $i => $s) {
            $no = $i + 1;
            $fields["no_$no"] = $no;
            $fields["nama_$no"] = $s->nama;
            $fields['nis_' . $no] = $s->nis;
            $fields['kamar_' . $no] = $s->kamar->nama_kamar ?? '-';
            $fields['kelas_pengajian_' . $no] = $s->namaKelasPengajian();
            $fields['kelas_sekolah_' . $no] = $s->namaKelasSekolah();
            $fields['jabatan_' . $no] = $s->jabatan ?? '-';
            $fields['is_pengajar_' . $no] = $s->is_pengajar ? 'Ya' : 'Tidak';
            $fields['whatsapp_' . $no] = $s->no_whatsapp ?: '-';
            $fields['hp_wali_' . $no] = $s->no_hp_wali ?: '-';
            $fields['alamat_' . $no] = $s->alamat_lengkap ?: ($s->alamat_singkat ?: '-');
        }
        $fields['jumlah_santri'] = $santris->count();

        $fdf = $this->buildFdf($fields);

        $filename = 'data_santri_' . now()->format('Y-m-d_H-i') . '.fdf';

        return response($fdf, 200)
            ->header('Content-Type', 'application/vnd.fdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename))
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /** Unduh data rekap Santri sebagai file Excel (.xlsx) yang asli. */
    public function exportExcel(Request $request)
    {
        $santris = $this->querySantriForExport($request)->get();

        $header = ['No', 'Nama', 'NIS', 'Kamar', 'Kelas Pengajian', 'Kelas Sekolah',
            'Jabatan', 'Pengajar', 'WhatsApp', 'HP Wali', 'Alamat Lengkap'];
        $baris = [];
        foreach ($santris as $i => $s) {
            $baris[] = [
                (string) ($i + 1),
                (string) $s->nama,
                (string) $s->nis,
                (string) ($s->kamar->nama_kamar ?? '-'),
                $s->namaKelasPengajian(),
                $s->namaKelasSekolah(),
                (string) ($s->jabatan ?: '-'),
                $s->is_pengajar ? 'Ya' : 'Tidak',
                (string) ($s->no_whatsapp ?: '-'),
                (string) ($s->no_hp_wali ?: '-'),
                (string) ($s->alamat_lengkap ?: ($s->alamat_singkat ?: '-')),
            ];
        }
        $baris[] = ['', 'Jumlah Santri', (string) $santris->count()];

        $filename = 'data_santri_' . now()->format('Y-m-d_H-i') . '.xlsx';
        $path = storage_path('app/' . $filename);

        $this->tulisXlsx($path, $header, $baris);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Nilai filter jenis kelamin yang sah dari request:
     * hanya "Laki-laki" atau "Perempuan"; nilai lain dikosongkan.
     */
    protected function filterJenisKelamin(Request $request): string
    {
        $jenisKelamin = (string) $request->get('filter_jenis_kelamin', '');

        if (!in_array($jenisKelamin, ['Laki-laki', 'Perempuan'], true)) {
            return '';
        }

        return $jenisKelamin;
    }

    /** Query dasar untuk export FDF & Excel (search, kamar, filter pengajar, jenis kelamin). */
    protected function querySantriForExport(Request $request)
    {
        $query = Santri::with('kamar');

        $search = $request->get('search', '');
        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%');
        }

        $kamarId = $request->get('kamar_id', '');
        if ($kamarId) {
            $query->where('kamar_id', $kamarId);
        }

        $filter = $request->get('filter', '');
        if ($filter === 'pengajar') {
            $query->where('is_pengajar', true);
        } elseif ($filter === 'santri') {
            $query->where('is_pengajar', false);
        }

        $jenisKelamin = $this->filterJenisKelamin($request);
        if ($jenisKelamin !== '' && Schema::hasColumn('santris', 'jenis_kelamin')) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        return $query->orderBy('nama', 'asc');
    }

    /** Tulis workbook .xlsx minimal (inline string) tanpa dependensi eksternal. */
    protected function tulisXlsx(string $path, array $header, array $baris): void
    {
        $xml = function (?string $teks): string {
            return htmlspecialchars((string) $teks, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        };

        $kolom = function (int $index): string {
            $huruf = '';
            while ($index > 0) {
                $sisa = ($index - 1) % 26;
                $huruf = chr(65 + $sisa) . $huruf;
                $index = intdiv($index - 1, 26);
            }

            return $huruf;
        };

        $jumlahKolom = max(count($header), ...array_map('count', $baris ?: [[]]));

        $sheetRows = '';
        $semua = array_merge([$header], $baris);
        foreach ($semua as $r => $barisSatu) {
            $cells = '';
            foreach ($barisSatu as $c => $nilai) {
                $ref = $kolom($c + 1) . ($r + 1);
                $style = $r === 0 ? ' s="1"' : '';
                if ($nilai === '' || $nilai === null) {
                    continue;
                }
                $cells .= '<c r="' . $ref . '"' . $style . ' t="inlineStr"><is><t xml:space="preserve">'
                    . $xml((string) $nilai) . '</t></is></c>';
            }
            $sheetRows .= '<row r="' . ($r + 1) . '">' . $cells . '</row>';
        }

        $parts = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
                . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="Data Santri" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                . '</Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font>'
                . '<font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
                . '<fills count="2"><fill><patternFill patternType="none"/></fill>'
                . '<fill><patternFill patternType="gray125"/></fill></fills>'
                . '<borders count="1"><border/></borders>'
                . '<cellStyleXfs count="1"><xf/></cellStyleXfs>'
                . '<cellXfs count="2"><xf xfId="0"/><xf xfId="0" fontId="1" applyFont="1"/></cellXfs>'
                . '</styleSheet>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<cols><col min="1" max="1" width="5" customWidth="1"/><col min="2" max="2" width="26" customWidth="1"/>'
                . '<col min="3" max="3" width="14" customWidth="1"/>'
                . '<col min="4" max="' . $jumlahKolom . '" width="20" customWidth="1"/></cols>'
                . '<sheetData>' . $sheetRows . '</sheetData></worksheet>',
        ];

        if (file_exists($path)) {
            @unlink($path);
        }

        $this->tulisZip($path, $parts);
    }

    /**
     * Buat arsip zip. Memakai ZipArchive bila tersedia; jika tidak,
     * ditulis manual (metode store/tanpa kompresi) agar tetap jalan.
     */
    protected function tulisZip(string $path, array $parts): void
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($parts as $nama => $isi) {
                    $zip->addFromString($nama, $isi);
                }
                $zip->close();

                return;
            }
        }

        $waktu = getdate();
        $dosTime = ($waktu['hours'] << 11) | ($waktu['minutes'] << 5) | ($waktu['seconds'] >> 1);
        $dosDate = (($waktu['year'] - 1980) << 9) | ($waktu['mon'] << 5) | $waktu['mday'];

        $isiBerkas = '';
        $direktori = '';
        $offset = 0;

        foreach ($parts as $nama => $isi) {
            $crc = crc32($isi);
            $panjang = strlen($isi);

            $header = "PK\x03\x04"
                . pack('v', 10) . pack('v', 0) . pack('v', 0)
                . pack('v', $dosTime) . pack('v', $dosDate)
                . pack('V', $crc) . pack('V', $panjang) . pack('V', $panjang)
                . pack('v', strlen($nama)) . pack('v', 0)
                . $nama . $isi;

            $direktori .= "PK\x01\x02"
                . pack('v', 0x0314) . pack('v', 10) . pack('v', 0) . pack('v', 0)
                . pack('v', $dosTime) . pack('v', $dosDate)
                . pack('V', $crc) . pack('V', $panjang) . pack('V', $panjang)
                . pack('v', strlen($nama)) . pack('v', 0) . pack('v', 0)
                . pack('v', 0) . pack('v', 0) . pack('V', 32) . pack('V', $offset)
                . $nama;

            $isiBerkas .= $header;
            $offset += strlen($header);
        }

        $jumlah = count($parts);
        $eocd = "PK\x05\x06"
            . pack('v', 0) . pack('v', 0) . pack('v', $jumlah) . pack('v', $jumlah)
            . pack('V', strlen($direktori)) . pack('V', $offset) . pack('v', 0);

        file_put_contents($path, $isiBerkas . $direktori . $eocd);
    }

    /**
     * Bangun form-data file FDF (PDF 1.7 Annex H) dengan struktur yang sama
     * seperti hasil "Export Form Data" Acrobat:
     *   - header %FDF-1.2 + penanda biner
     *   - objek 1 = dictionary /FDF dengan /Fields berupa objek tidak langsung
     *   - tiap field = /Type /Annot /Subtype /Widget + /FT /Tx + /T + /V
     *   - trailer /Root + /ID (dua hex string)
     * Nilai non-ASCII ditulis sebagai hex string UTF-16BE (dengan BOM) agar
     * nama dari database tidak rusak.
     */
    protected function buildFdf(array $fields): string
    {
        $referensi = [];
        $objek = [];
        $nomor = 1;

        foreach ($fields as $key => $value) {
            $nomor++;
            $referensi[] = $nomor . ' 0 R';
            $objek[$nomor] = "<<\n/Type /Annot\n/Subtype /Widget\n/FT /Tx\n/Ff 0\n/T "
                . $this->fdfString((string) $key)
                . "\n/V " . $this->fdfString((string) $value) . "\n>>";
        }

        // %#E2#E3#CFD3 = penanda biner yang ditulis Adobe di baris kedua.
        $fdf = "%FDF-1.2\n%\xE2\xE3\xCF\xD3\n";
        $fdf .= "1 0 obj\n<<\n/FDF\n<<\n/Fields [ " . implode(' ', $referensi) . " ]\n>>\n>>\nendobj\n";
        foreach ($objek as $num => $isi) {
            $fdf .= $num . " 0 obj\n" . $isi . "\nendobj\n";
        }
        $fdf .= "trailer\n<<\n/Root 1 0 R\n/ID [ <" . md5('santri-fdf-a') . "> <" . md5('santri-fdf-b') . "> ]\n>>\n%%EOF\n";

        return $fdf;
    }

    /**
     * String PDF: ASCII teks biasa ditulis literal dengan escape,
     * teks non-ASCII ditulis sebagai hex string UTF-16BE + BOM.
     */
    protected function fdfString(string $teks): string
    {
        $teks = preg_replace('/[\r\n\t]+/', ' ', $teks);
        $teks = str_replace(["\r"], ['\\r'], $teks);

        if (preg_match('/[^\x20-\x7E]/', $teks)) {
            return '<' . strtoupper(bin2hex("\xFE\xFF" . mb_convert_encoding($teks, 'UTF-16BE', 'UTF-8'))) . '>';
        }

        $teks = str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $teks
        );

        return '(' . $teks . ')';
    }

    public function exportCetak(Request $request)
    {
        $search = $request->get('search', '');
        $kamarId = $request->get('kamar_id', '');
        $filter = $request->get('filter', '');
        $jenisKelamin = $this->filterJenisKelamin($request);

        $query = Santri::with('kamar');
        if ($search) $query->where('nama', 'like', '%' . $search . '%');
        if ($kamarId) $query->where('kamar_id', $kamarId);
        if ($filter === 'pengajar') $query->where('is_pengajar', true);
        elseif ($filter === 'santri') $query->where('is_pengajar', false);
        if ($jenisKelamin !== '' && Schema::hasColumn('santris', 'jenis_kelamin')) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        $santris = $query->orderBy('nama', 'asc')->get();

        return view('santri.cetak', compact('santris', 'search', 'kamarId', 'filter', 'jenisKelamin'));
    }
}
