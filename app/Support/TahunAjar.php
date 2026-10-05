<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Utilitas tahun ajar & naik kelas.
 *
 * Tahun ajar berganti pada 1 Juli, jadi 1 Juli 2026 -> "2026/2027"
 * dan 1 Juni 2027 -> "2026/2027" (masih semester genap tahun sebelumnya).
 * Semua perhitungan memakai tanggal lokal aplikasi.
 */
class TahunAjar
{
    /** Daftar jenjang yang dikenali, urut dari jenjang terendah. */
    public const JENJANG = [
        'Ibtida' => 1,
        'Sanawiyah' => 2,
        'SMP' => 3,
        'MTs' => 3,
        'SMA' => 4,
        'MA' => 4,
    ];

    /** Tingkat tertinggi per jenjang (11/12 untuk SMA/MA). */
    public const TINGKAT_MAKS = [
        'Ibtida' => 6,
        'Sanawiyah' => 6,
        'SMP' => 9,
        'MTs' => 9,
        'SMA' => 12,
        'MA' => 12,
    ];

    /** Tahun ajar yang sedang berjalan, mis. "2026/2027". */
    public static function sekarang(?Carbon $tanggal = null): string
    {
        $tanggal = $tanggal ? $tanggal->copy() : Carbon::now();

        // Tahun ajar baru mulai 1 Juli.
        if ($tanggal->month >= 7) {
            return $tanggal->year . '/' . ($tanggal->year + 1);
        }

        return ($tanggal->year - 1) . '/' . $tanggal->year;
    }

    /** Tahun ajar setelah $tahunAjar, mis. "2026/2027" → "2027/2028". */
    public static function berikutnya(string $tahunAjar): string
    {
        $awal = self::awalTahun($tahunAjar);

        return ($awal + 1) . '/' . ($awal + 2);
    }

    /** Tahun ajar sebelum $tahunAjar, atau null bila tidak terformat. */
    public static function sebelumnya(string $tahunAjar): ?string
    {
        $awal = self::awalTahun($tahunAjar);

        return ($awal - 1) . '/' . $awal;
    }

    /** Tahun awal dari label "2026/2027". Null bila tidak valid. */
    public static function awalTahun(?string $tahunAjar): ?int
    {
        if (!is_string($tahunAjar)) {
            return null;
        }

        if (preg_match('/(\d{4})\s*\/\s*(\d{4})/', $tahunAjar, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/^\s*(\d{4})\s*$/', $tahunAjar, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /** Label tahun ajar valid, atau null. */
    public static function valid(?string $tahunAjar): bool
    {
        return self::awalTahun($tahunAjar) !== null;
    }

    /**
     * Pecah nama kelas menjadi jenjang + tingkat.
     *
     * Contoh:
     *   "7 SMP"      => ['jenjang' => 'SMP',  'tingkat' => 7,  'sisa' => '']
     *   "10 SMA"     => ['jenjang' => 'SMA',  'tingkat' => 10, 'sisa' => '']
     *   "Ibtida 1"   => ['jenjang' => 'Ibtida','tingkat' => 1,  'sisa' => '']
     *   "1A"         => ['jenjang' => null,   'tingkat' => 1,  'sisa' => 'A']
     *   "XI IPA 1"   => ['jenjang' => 'SMA',  'tingkat' => 11, 'sisa' => 'IPA 1']
     */
    public static function parseNamaKelas(string $nama): array
    {
        $hasil = self::petikNamaKelas(trim($nama));

        // Tanpa jenjang eksplisit, angka >= 10 hampir pasti SMA/MA: SMP dan MTs
        // maksimum kelas 9, sedangkan nama kelas SMA sering ditulis ringkas
        // seperti "11 IPA". Tanpa ini,jenjangnya null dan naik kelas berhenti
        // di tingkat 99 sehingga tidak pernah menemukan batasnya.
        if ($hasil['jenjang'] === null && $hasil['tingkat'] !== null && $hasil['tingkat'] >= 10) {
            $hasil['jenjang'] = 'SMA';
        }

        return $hasil;
    }

    private static function petikNamaKelas(string $nama): array
    {
        $hasil = ['jenjang' => null, 'tingkat' => null, 'sisa' => ''];

        if ($nama === '') {
            return $hasil;
        }

        // 1) Angka di depan, contoh "7 SMP", "10 SMA", "1A"
        if (preg_match('/^(\d{1,2})\s*(.*)$/u', $nama, $m)) {
            $hasil['tingkat'] = (int) $m[1];
            $sisa = trim($m[2]);
            foreach (self::JENJANG as $jenjang => $_) {
                if (stripos($sisa, $jenjang) === 0) {
                    $hasil['jenjang'] = $jenjang;
                    $hasil['sisa'] = trim(substr($sisa, strlen($jenjang)));
                    return $hasil;
                }
            }
            $hasil['sisa'] = $sisa;
            return $hasil;
        }

        // 2) Jenjang lebih dulu, contoh "Ibtida 1", "Sanawiyah 2", "SMP 7"
        foreach (self::JENJANG as $jenjang => $_) {
            if (stripos($nama, $jenjang) === 0) {
                $sisa = trim(substr($nama, strlen($jenjang)));
                $hasil['jenjang'] = $jenjang;
                if (preg_match('/^(\d{1,2})\s*(.*)$/u', $sisa, $m)) {
                    $hasil['tingkat'] = (int) $m[1];
                    $hasil['sisa'] = trim($m[2]);
                } else {
                    $hasil['sisa'] = $sisa;
                }
                return $hasil;
            }
        }

        // 3) Angka Romawi, contoh "XI IPA 1", "XII IPA 2"
        if (preg_match('/^(XII|XI|IX|VIII|VII|VI|IV|V|III|II|I)\s*(.*)$/u', $nama, $m)) {
            $hasil['jenjang'] = 'SMA';
            $hasil['tingkat'] = self::romawi()[$m[1]] ?? null;
            $hasil['sisa'] = trim($m[2]);
            return $hasil;
        }

        return $hasil;
    }

    /** Peta angka Romawi kapital yang dipakai di nama kelas. */
    private static function romawi(): array
    {
        return ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6,
            'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12];
    }

    /**
     * Nama kelas satu tingkat di atas.
     *
     * Lulus SMP/MTs naik ke SMA tingkat 10 (pergantian jenjang), jadi
     * "9 SMP" → "10 SMA", bukan null. Setelah itu naik satu tingkat seperti
     * biasa sampai batas jenjang masing-masing.
     *
     * Return null bila tidak bisa ditebak otomatis (mis. sudah tingkat
     * tertinggi, atau nama kelas tanpa pola angka) — pemanggil harus
     * meminta nama tujuan ke pengguna.
     */
    public static function namaKelasBerikutnya(?string $nama): ?string
    {
        if (!$nama) {
            return null;
        }

        $p = self::parseNamaKelas($nama);
        if ($p['tingkat'] === null) {
            return null;
        }

        // Lulus SMP/MTs → SMA/MA.
        if (($p['jenjang'] === 'SMP' || $p['jenjang'] === 'MTs') && $p['tingkat'] >= 9) {
            return '10 SMA';
        }

        $maks = $p['jenjang'] ? (self::TINGKAT_MAKS[$p['jenjang']] ?? 99) : 99;
        if ($p['tingkat'] >= $maks) {
            return null; // sudah tamat / tidak ada tingkat berikutnya
        }

        $baru = $p['tingkat'] + 1;
        $sisa = $p['sisa'];

        // Pertahankan gaya penulisan asal supaya nama kelas tidak melompat
        // format: "XI IPA 1" → "XII IPA 1", bukan "12 SMA IPA 1".
        if (preg_match('/^(XII|XI|IX|VIII|VII|VI|IV|V|III|II|I)\b/u', trim($nama), $rm)) {
            $romawiBerikut = array_search($baru, self::romawi(), true);

            return trim(($romawiBerikut !== false ? $romawiBerikut : $baru)
                . ($sisa !== '' ? ' ' . $sisa : ''));
        }

        if ($p['jenjang']) {
            // "Ibtida 1" → "Ibtida 2", bukan "2 Ibtida".
            $jenjangDiDepan = stripos(trim($nama), $p['jenjang']) === 0;
            $potongan = ($jenjangDiDepan ? $p['jenjang'] . ' ' . $baru : $baru . ' ' . $p['jenjang'])
                . ($sisa !== '' ? ' ' . $sisa : '');

            return trim($potongan);
        }

        return trim((string) $baru . $sisa);
    }

    /** Tingkat berikutnya (null bila sudah maksimum). */
    public static function tingkatBerikutnya(?int $tingkat, ?string $jenjang): ?int
    {
        if ($tingkat === null) {
            return null;
        }

        if (($jenjang === 'SMP' || $jenjang === 'MTs') && $tingkat >= 9) {
            return 10; // lulus SMP/MTs → SMA
        }

        $maks = $jenjang ? (self::TINGKAT_MAKS[$jenjang] ?? 99) : 99;

        return $tingkat >= $maks ? null : $tingkat + 1;
    }

    /** Jenis kelas: naik otomatis tiap tahun, atau tetap (kelas pengajian). */
    public const JENIS_FORMAL = 'formal';

    public const JENIS_PENGAJIAN = 'pengajian';

    /**
     * Kata kunci yang menandai kelas pengajian (majelis/kajian), dipakai kalau
     * nama kelasnya sendiri sudah menyebut jenisnya.
     */
    private const KUNCI_PENGAJIAN = ['takhosus', 'takhossus', 'pengajian', 'kajian', 'majelis', 'halaqah', 'halakah'];

    /**
     * Tebakan jenis kelas dari namanya.
     *
     * Kelas pengajian di pesantren tidak naik tingkat: "Kelas 1" tahun ini
     * tetap "Kelas 1" tahun depan, begitu juga Takhosus I/II. Sebaliknya
     * kelas sekolah ("1 SMP", "Ibtida 3", "XI IPA 1") memang naik satu
     * tingkat tiap tahun.
     *
     * Heuristiknya:
     *   1) nama menyebut pengajian/takhosus/...        → pengajian
     *   2) ada jenjang ("SMP", "Ibtida", ...)          → formal
     *   3) angka polos tanpa teks ("1".."9")           → pengajian
     *   4) angka + teks ("1A", "10 IPA")               → formal
     *   5) tanpa angka sama sekali ("Takhosus", ...)  → pengajian
     *
     * Ini hanya TEBAKAN awal. Stored value di `kelas_master.jenis` yang
     * dipakai aplikasi, dan bisa diubah sendiri lewat toggle di halaman
     * /kelas — jadi kelas yang salah terdeteksi tidak merusak data.
     */
    public static function deteksiJenis(string $nama): string
    {
        $nama = trim($nama);

        if ($nama === '') {
            return self::JENIS_FORMAL;
        }

        $kecil = mb_strtolower($nama);

        foreach (self::KUNCI_PENGAJIAN as $kunci) {
            if (str_contains($kecil, $kunci)) {
                return self::JENIS_PENGAJIAN;
            }
        }

        $p = self::petikNamaKelas($nama);

        if ($p['jenjang'] !== null) {
            return self::JENIS_FORMAL;
        }

        if ($p['tingkat'] !== null) {
            return $p['sisa'] === '' ? self::JENIS_PENGAJIAN : self::JENIS_FORMAL;
        }

        return self::JENIS_PENGAJIAN;
    }

    /** Daftar jenis kelas yang valid. */
    public static function jenisValid(?string $jenis): bool
    {
        return in_array($jenis, [self::JENIS_FORMAL, self::JENIS_PENGAJIAN], true);
    }

    /** Ringkas info kelas untuk ditampilkan. */
    public static function ringkas(?string $nama): string
    {
        $p = self::parseNamaKelas((string) $nama);
        $bagian = [];
        if ($p['tingkat'] !== null) {
            $bagian[] = 'tingkat ' . $p['tingkat'];
        }
        if ($p['jenjang']) {
            $bagian[] = $p['jenjang'];
        }
        if ($p['sisa'] !== '') {
            $bagian[] = $p['sisa'];
        }

        return implode(' · ', $bagian);
    }
}
