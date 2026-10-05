<?php

namespace App\Models;

use App\Support\TahunAjar;
use Illuminate\Database\Eloquent\Model;

class KelasMaster extends Model
{
    protected $table = 'kelas_master';

    protected $fillable = ['tahun_ajar', 'nama', 'jenjang', 'tingkat', 'sisa', 'jenis', 'kapasitas', 'aktif'];

    protected $casts = [
        'tingkat' => 'integer',
        'kapasitas' => 'integer',
        'aktif' => 'boolean',
    ];

    /** Nilai `jenis` yang tersedia. */
    public const FORMAL = TahunAjar::JENIS_FORMAL;

    public const PENGAJIAN = TahunAjar::JENIS_PENGAJIAN;

    /** Patokan jumlah orang per kelas pengajian (±20). */
    public const KAPASITAS_PENGAJIAN = 20;

    public function santris()
    {
        return $this->hasMany(Santri::class);
    }

    /**
     * Santri yang punya kelas INI sebagai kelas pengajian.
     *
     * Sekarang satu Santri punya dua jalur kelas sekaligus, jadi `santris()`
     * saja tidak cukup: kelas pengajian dihitung dari kolom khusus pengajian,
     * kelas sekolah dari kolom khusus sekolah.
     */
    public function santrisPengajian()
    {
        return $this->hasMany(Santri::class, 'kelas_pengajian_id');
    }

    public function santrisSekolah()
    {
        return $this->hasMany(Santri::class, 'kelas_sekolah_id');
    }

    public function scopeTahunAjar($query, string $tahunAjar)
    {
        return $query->where('tahun_ajar', $tahunAjar);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopePengajian($query)
    {
        return $query->where('jenis', self::PENGAJIAN);
    }

    public function scopeFormal($query)
    {
        return $query->where('jenis', self::FORMAL);
    }

    /**
     * Kelas pengajian tidak naik tingkat saat tahun ajar berganti.
     *
     * Nilai `jenis` yang kosong (baris lama sebelum migrasi) diperlakukan
     * sebagai pengajian kalau namanya terdeteksi demikian, supaya kelas
     * 1-6 dan Takhosus tidak ikut naik walau migrasi belum sempat jalan.
     */
    public function isPengajian(): bool
    {
        if (!empty($this->jenis)) {
            return $this->jenis === self::PENGAJIAN;
        }

        return TahunAjar::deteksiJenis((string) $this->nama) === self::PENGAJIAN;
    }

    /** Label jenis untuk ditampilkan. */
    public function labelJenis(): string
    {
        return $this->isPengajian() ? 'Pengajian' : 'Formal';
    }

    /** Nama kelas satu tingkat di atas (null bila tidak bisa ditebak). */
    public function namaKelasBerikutnya(): ?string
    {
        return TahunAjar::namaKelasBerikutnya($this->nama);
    }

    /**
     * Kelas tujuan saat tahun ajar berganti.
     *
     * Pengajian tetap memakai nama yang sama; kelas formal naik satu tingkat,
     * atau disalin dengan nama sama kalau tingkatnya tidak bisa ditebak
     * (mis. sudah kelas terakhir jenjangnya).
     */
    public function namaKelasTahunBerikutnya(): string
    {
        if ($this->isPengajian()) {
            return $this->nama;
        }

        return $this->namaKelasBerikutnya() ?? $this->nama;
    }

    /**
     * Ringkasan untuk ditampilkan: "tingkat 7 · SMP · 12 santri".
     */
    public function ringkas(): string
    {
        $ket = TahunAjar::ringkas($this->nama);
        $jumlah = $this->jumlahSantri();
        $bagian = $ket !== '' ? $ket : $this->labelJenis();
        $jml = $jumlah . ' santri';

        return $this->isPengajian() ? $bagian . ' · pengajian · ' . $jml : $bagian . ' · ' . $jml;
    }

    /**
     * Jumlah Santri pada kelas ini, baik sebagai pengajian maupun sekolah.
     *
     * Kedua jalur punya foreign key masing-masing, jadi harus dihitung
     * terpisah lalu dijumlahkan. Satu Santri tidak mungkin terhitung dua kali
     * untuk kelas yang sama.
     */
    public function jumlahSantri(): int
    {
        if (! $this->id) {
            return 0;
        }

        return $this->santrisPengajian()->count() + $this->santrisSekolah()->count();
    }

    /**
     * Patokan jumlah orang di kelas ini, atau null kalau tidak ada.
     *
     * Kelas pengajian default memakai 20 orang kalau kolomnya kosong, supaya kelas
     * yang dibuat sebelum migrasi ini jalan tetap punya patokan. Kelas sekolah
     * tidak punya jumlah ideal sehingga null (tidak ditampilkan).
     */
    public function kapasitasEfektif(): ?int
    {
        if ($this->kapasitas !== null) {
            return (int) $this->kapasitas;
        }

        return $this->isPengajian() ? self::KAPASITAS_PENGAJIAN : null;
    }

    /**
     * Berapa kursi yang masih kosong sebelum kelas ini dianggap penuh.
     *
     * Negative berarti sudah lewat patokan. Itu tidak otomatis salah: di
     * pengajian memang ada Santri yang belum layak naik sehingga kelasnya
     * bisa Temporary penuh. Return null kalau kelas ini tidak punya patokan.
     */
    public function sisaKursi(): ?int
    {
        $kapasitas = $this->kapasitasEfektif();

        if ($kapasitas === null) {
            return null;
        }

        return $kapasitas - $this->jumlahSantri();
    }

    /** Kelas pengajian sudah lewat / mencapai patokan jumlah orang. */
    public function sudahPenuh(): bool
    {
        $sisa = $this->sisaKursi();

        return $sisa !== null && $sisa <= 0;
    }

    /**
     * Urut dari yang paling rendah ke tertinggi untuk kelas pengajian.
     *
     * Pengajian tidak punya jenjang/tingkat resmi, jadi urutannya dibaca dari
     * namanya: angka biasanya (Kelas 1, 2, 3, ...) lebih dulu, lalu Romawi
     * (Takhosus I, Takhosus II, ...). Dipakai supaya tabel di halaman /kelas
     * menampilkan urutan yang sudah benar tanpa perlu admin mengurutkan manual.
     */
    public static function urutPengajian(array $kelas): array
    {
        $urut = [];

        foreach ($kelas as $i => $k) {
            $nama = trim(preg_replace('/^kelas\s+/i', '', (string) $k->nama));

            $angka = [];
            $romawi = null;

            if (preg_match_all('/\d+/', $nama, $angka)) {
                $angka = array_map('intval', $angka[0]);
            }

            if (preg_match('/\b(XII|XI|IX|VIII|VII|VI|IV|V|III|II|I)\b/u', strtoupper($nama), $r)) {
                $romawi = array_search($r[1], ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'], true);
            }

            // Tanpa angka maupun Romawi (mis. "Majelis A") diurutkan paling akhir.
            $urut[$i] = [$angka === [] ? PHP_INT_MAX : (int) $angka[0], $romawi ?? PHP_INT_MAX, $nama];
        }

        $order = array_keys($kelas);
        array_multisort($order, SORT_ASC, $urut);

        return array_map(fn ($i) => $kelas[$i], $order);
    }

    /**
     * Cari (atau buat) kelas master pada tahun ajar tertentu.
     *
     * Dipakai saat menyimpan form Data Santri supaya kolom teks
     * (kelas_pengajian / kelas_sekolah) dan foreign key-nya selalu konsisten.
     *
     * Jenis yang diminta dipakai kalau nama barunya benar-benar baru dibuat.
     * Kalau nama yang sama sudah ada dengan jenis lain (mis. "1" sudah jadi
     * pengajian, lalu dipakai sebagai kelas sekolah), baris lama dikembalikan
     * apa adanya — menimpa jenisnya bisa membuat seluruh Santri di kelas itu
     * ikut berubah perilaku naiknya.
     */
    public static function findOrBuat(string $nama, ?string $tahunAjar = null, ?string $jenis = null): ?self
    {
        $nama = trim($nama);
        if ($nama === '') {
            return null;
        }

        $tahunAjar = $tahunAjar && TahunAjar::valid($tahunAjar)
            ? $tahunAjar
            : TahunAjar::sekarang();

        $ada = static::where('tahun_ajar', $tahunAjar)->where('nama', $nama)->first();
        if ($ada) {
            return $ada;
        }

        $p = TahunAjar::parseNamaKelas($nama);

        return static::create([
            'tahun_ajar' => $tahunAjar,
            'nama' => $nama,
            'jenjang' => $p['jenjang'],
            'tingkat' => $p['tingkat'],
            'sisa' => $p['sisa'] !== '' ? $p['sisa'] : null,
            'jenis' => TahunAjar::jenisValid($jenis) ? $jenis : TahunAjar::deteksiJenis($nama),
            'aktif' => true,
        ]);
    }

    /**
     * Daftar kelas untuk dropdown form Santri, dikelompokkan per tahun ajar
     * lalu dipisah pengajian vs sekolah supaya blade tidak perlu query.
     *
     * @return array<string, array{pengajian: \Illuminate\Support\Collection, sekolah: \Illuminate\Support\Collection}>
     */
    public static function untukDropdown(?string $tahunAjar = null): array
    {
        if (! static::query()->exists()) {
            return [];
        }

        $query = static::query()
            ->orderByDesc('tahun_ajar')
            ->orderBy('tingkat')
            ->orderBy('nama');

        if ($tahunAjar && TahunAjar::valid($tahunAjar)) {
            $query->where('tahun_ajar', $tahunAjar);
        }

        $kelompok = [];
        foreach ($query->get() as $k) {
            $kelompok[$k->tahun_ajar][$k->isPengajian() ? 'pengajian' : 'sekolah'][] = $k;
        }

        return array_map(function ($isi) {
            return [
                'pengajian' => collect($isi['pengajian'] ?? []),
                'sekolah' => collect($isi['sekolah'] ?? []),
            ];
        }, $kelompok);
    }

    /** Daftar tahun ajar yang ada di sistem, terbaru dulu. */
    public static function daftarTahunAjar(): array
    {
        return static::query()
            ->distinct()
            ->orderByDesc('tahun_ajar')
            ->pluck('tahun_ajar')
            ->all();
    }
}
