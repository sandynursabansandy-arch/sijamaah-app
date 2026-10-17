<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Santri extends Model
{
    protected $fillable = [
        'nis', 'nama', 'jenis_kelamin', 'kamar_id', 'kelas', 'kelas_master_id',
        'kelas_pengajian', 'kelas_pengajian_id',
        'kelas_sekolah', 'kelas_sekolah_id',
        'jabatan', 'is_pengajar',
        'no_hp_wali', 'no_whatsapp', 'status',
        'alamat_provinsi', 'alamat_kabupaten', 'alamat_kecamatan',
        'alamat_desa', 'alamat_dusun', 'alamat_rt', 'alamat_rw',
        'alamat_jalan', 'alamat_nomor_rumah', 'kode_pos',
        'alamat_lengkap', 'latitude', 'longitude', 'foto_rumah'
    ];

    protected $appends = ['whatsapp_url', 'kelas_gabungan'];

    protected $casts = [
        'is_pengajar' => 'boolean',
    ];

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function kelasMaster(): BelongsTo
    {
        return $this->belongsTo(KelasMaster::class);
    }

    /** Relasi ke master kelas pengajian (kelas majelis). */
    public function kelasPengajianMaster(): BelongsTo
    {
        return $this->belongsTo(KelasMaster::class, 'kelas_pengajian_id');
    }

    /** Relasi ke master kelas sekolah (kelas formal). */
    public function kelasSekolahMaster(): BelongsTo
    {
        return $this->belongsTo(KelasMaster::class, 'kelas_sekolah_id');
    }

    /**
     * Nama kelas pengajian, atau teks yang dietik manual kalau master-nya belum
     * ada. Nilai teks di database yang jadi sumber kebenaran supaya kelas yang
     * belum terdaftar di master tetap tampil.
     */
    public function namaKelasPengajian(): string
    {
        $nama = trim((string) ($this->kelas_pengajian ?? ''));

        return $nama !== '' ? $nama : '-';
    }

    /** Nama kelas sekolah, atau teks manual. Sama seperti di atas. */
    public function namaKelasSekolah(): string
    {
        $nama = trim((string) ($this->kelas_sekolah ?? ''));

        return $nama !== '' ? $nama : '-';
    }

    // Koreksi jenis kelamin dari nama (token => jenis
    // kelamin), hasil verifikasi user 13 Okt 2026.
    // Diutamakan atas tebakan otomatis.
    private const KOREKSI_JENIS_KELAMIN = [
        'ramdani' => 'Laki-laki',
        'ramdhani' => 'Laki-laki',
        'sihabudin' => 'Laki-laki',
        'sihabuddin' => 'Laki-laki',
        'rino' => 'Laki-laki',
        'mubarok' => 'Laki-laki',
        'wahyu' => 'Laki-laki',
        'wahyudi' => 'Laki-laki',
        'rini' => 'Perempuan',
        'apriliani' => 'Perempuan',
        'rifa' => 'Perempuan',
        'muslimah' => 'Perempuan',
        'rhania' => 'Perempuan',
        'marta' => 'Perempuan',
        'sita' => 'Perempuan',
        'rayagina' => 'Perempuan',
        'aeni' => 'Perempuan',
    ];

    // Token nama yang lazim dipakai perempuan / laki-laki
    // (huruf kecil), dipakai tebakJenisKelamin().
    private const NAMA_PEREMPUAN = [
        'adiba', 'adilah', 'afifah', 'aisha', 'aishah', 'aisyah', 'ajeng',
        'alifa', 'alifah', 'amirah', 'anisa', 'anissa', 'anggraeni', 'anggraaini',
        'aqila', 'aqilah', 'arifah', 'ayu', 'azizah', 'badiah', 'badriyah',
        'bahiyah', 'barkah', 'bilqis', 'cahyati', 'cici', 'diah', 'dina',
        'dini', 'dira', 'dewi', 'elis', 'eni', 'era', 'erni', 'esty', 'eva',
        'fatimah', 'firda', 'fitri', 'fitria', 'fitriani', 'futri', 'gita',
        'heni', 'hafidha', 'hartati', 'hidayah', 'idayah', 'ika', 'ikah',
        'imah', 'indah', 'irma', 'isma', 'jannah', 'juni', 'juita', 'kamilah',
        'kania', 'kayla', 'khadijah', 'khaulah', 'khoiriyah', 'kumala',
        'laila', 'lailatul', 'lala', 'laras', 'latifah', 'lestari', 'lia',
        'lili', 'lisa', 'lita', 'luthfia', 'maesaroh', 'maghfurah', 'malika',
        'mardiyah', 'maria', 'mariyam', 'marwah', 'masturah', 'masitoh',
        'maryam', 'maya', 'mega', 'melati', 'melisa', 'mia', 'mila', 'minah',
        'mirna', 'misnah', 'mubarokah', 'mulia', 'munawaroh', 'musfiroh',
        'nabila', 'nadia', 'nafisah', 'najwa', 'nanda', 'nani', 'nania',
        'nawang', 'nazwa', 'neni', 'neny', 'nisa', 'nisfa', 'nila', 'nilam',
        'nurlaila', 'nuraini', 'nurjanah', 'nurhayati', 'nurhidayah',
        'nurmala', 'nurrohmah', 'nursyifa', 'nyimas', 'paramitha', 'pratiwi',
        'puspita', 'puti', 'putri', 'rahayu', 'rahma', 'rahmawati', 'ratih',
        'ratna', 'restu', 'retno', 'reva', 'ria', 'riani', 'rida', 'rizkia',
        'risma', 'rismahwati', 'rita', 'rohmah', 'rosidah', 'rosihah',
        'rubaidah', 'sabrina', 'saadah', 'safitri', 'sakinah', 'salmah',
        'salsa', 'salsabila', 'salsabilla', 'salwa', 'samidah', 'sari',
        'sariyah', 'sasmi', 'saudah', 'sela', 'selvi', 'septi', 'shafira',
        'sholihah', 'solihah', 'siti', 'sodiqah', 'sri', 'sukma', 'suci',
        'sumarni', 'sumi', 'susanti', 'sutarni', 'tania', 'tanti', 'tasya',
        'tahani', 'tahira', 'tia', 'tiani', 'tika', 'tutik', 'ucy', 'umy',
        'unayah', 'urifah', 'utami', 'vani', 'vera', 'veronica', 'via',
        'vina', 'vivi', 'wafiqah', 'wahyuni', 'wanda', 'wardah', 'wati',
        'widya', 'wika', 'winda', 'windi', 'wini', 'winingsih', 'wulandari',
        'yani', 'yanti', 'yasinah', 'yelda', 'yola', 'yulia', 'yuliana',
        'yulianti', 'yunita', 'yuniarti', 'yunidar', 'yumna', 'zahra',
        'zahrah', 'zahwa', 'zainab', 'zahira', 'zulfa', 'zulaikha', 'zuliani',
    ];

    private const NAMA_LAKI = [
        'abdul', 'abdulaziz', 'abdulhadi', 'abduljalil', 'abduljamil',
        'abdulkarim', 'abdulmalik', 'abdulrahim', 'abdurrahman',
        'abdulrahman', 'abdulwahab', 'abdulwahid', 'abdullah', 'abu',
        'agus', 'ahmad', 'akbar', 'alif', 'anwar', 'andi', 'aqil', 'arif',
        'arifin', 'aziz', 'azizan', 'badrun', 'bagas', 'bambang', 'bahrul',
        'bahrun', 'bilal', 'dani', 'dany', 'danu', 'darma', 'dedi', 'dimas',
        'eko', 'fachri', 'fajar', 'fathan', 'faisal', 'faiz', 'faizal',
        'fauzan', 'fauzi', 'feri', 'fiqri', 'firdaus', 'galih', 'ghani',
        'ghufron', 'habib', 'haidar', 'hadi', 'hafidz', 'hafiz', 'hafizh',
        'hamzah', 'hanafi', 'hanif', 'haris', 'harits', 'hasan', 'hasbi',
        'haziq', 'hendro', 'hilal', 'hidayat', 'hudan', 'ibrahim', 'idris',
        'ikbal', 'ilham', 'irfan', 'iqbal', 'jafar', 'jalal', 'jaya',
        'joko', 'joni', 'jundullah', 'kamil', 'kamal', 'karim', 'kazim',
        'khalid', 'kholid', 'khoirul', 'khusnul', 'labib', 'latif',
        'luqman', 'luthfi', 'lukman', 'majid', 'malik', 'maliki', 'mansur',
        'maulana', 'marwan', 'masrur', 'miftah', 'misbah', 'moh', 'mohamad',
        'mohammad', 'muh', 'muhamad', 'muhammad', 'muhajir', 'mushlich',
        'najib', 'nashrullah', 'nasir', 'naufal', 'nazir', 'nizar', 'omar',
        'oka', 'pasha', 'parhan', 'purnomo', 'qadri', 'qasim', 'qomar',
        'qurayshi', 'rafif', 'rafli', 'rahman', 'ramadhan', 'razan',
        'rasyid', 'rayhan', 'ridho', 'ridlwan', 'ridwan', 'rifai', 'rifaldi',
        'rizal', 'rizki', 'rizky', 'rizwan', 'ruhul', 'rudi', 'sahrul',
        'saif', 'saifullah', 'salam', 'sami', 'samir', 'salim', 'shadiq',
        'sharif', 'shiddiq', 'sholeh', 'syafiq', 'syahrul', 'syahril',
        'syamsul', 'syarif', 'syarifuddin', 'syukri', 'syukur', 'sigit',
        'slamet', 'sukarno', 'suwarno', 'subagio', 'sudarmo', 'sudiro',
        'sumarno', 'sutrisno', 'suyoto', 'tahir', 'tamim', 'tarmizi',
        'taufik', 'tejo', 'tarno', 'tono', 'tsabit', 'tsoriq', 'ujang',
        'umayr', 'umar', 'urwah', 'usman', 'utsman', 'vicky', 'vino',
        'wafi', 'wahid', 'wail', 'walid', 'waskito', 'wawan', 'wibowo',
        'wibisono', 'widi', 'wicaksono', 'wiro', 'yadi', 'yahya', 'yakub',
        'yamin', 'yazid', 'yoga', 'yulianto', 'yulius', 'yusak', 'yusuf',
        'yustian', 'zaelani', 'zakaria', 'zaki', 'zakiy', 'zainal', 'zaini',
        'zainuddin', 'zamroni', 'zamzami', 'zidan', 'zaidan', 'zahid',
        'zahir', 'zuhri', 'zuhairi', 'zulfaqar', 'zulfikar', 'zulham',
        'zulqarnain', 'zulkifli', 'zulkarnain', 'putra', 'bin',
    ];

    /**
     * Perkiraan jenis kelamin dari nama santri, dipakai
     * melengkapi kolom cetak bila `jenis_kelamin` masih
     * kosong. Hanya tebakan dari nama yang lazim dipakai;
     * nama tanpa petunjuk diasumsikan laki-laki.
     */
    public function tebakJenisKelamin(): string
    {
        $nama = strtolower((string) ($this->nama ?? ''));

        $kata = preg_split('/[^\p{L}\p{N}]+/u', $nama, -1, PREG_SPLIT_NO_EMPTY);

        // Koreksi manual (hasil verifikasi user) didahulukan.
        foreach ($kata as $token) {
            if (isset(self::KOREKSI_JENIS_KELAMIN[$token])) {
                return self::KOREKSI_JENIS_KELAMIN[$token];
            }
        }

        // Tanda pasti: putra/bin (laki-laki), putri/binti (perempuan).
        if (preg_match('/\b(putri|binti|bint)\b/', $nama)) {
            return 'Perempuan';
        }
        if (preg_match('/\b(putra|bin)\b/', $nama)) {
            return 'Laki-laki';
        }

        $skorPerempuan = 0;
        $skorLaki = 0;
        foreach ($kata as $token) {
            if (in_array($token, self::NAMA_PEREMPUAN, true)) {
                $skorPerempuan++;
            }
            if (in_array($token, self::NAMA_LAKI, true)) {
                $skorLaki++;
            }
        }

        return $skorPerempuan > $skorLaki ? 'Perempuan' : 'Laki-laki';
    }

    /**
     * Nilai `no_whatsapp` bila berisi nomor telepon. Kolom ini
     * ada yang terisi nama kabupaten/kota (salah input atau
     * impor), jadi nilai yang memuat huruf bukan nomor.
     */
    public function noWhatsappTelepon(): string
    {
        $nilai = trim((string) ($this->no_whatsapp ?? ''));

        if ($nilai === '' || preg_match('/\p{L}/u', $nilai)) {
            return '';
        }

        return $nilai;
    }

    /**
     * Nama kabupaten/kota untuk cetak: nilai yang tersimpan di
     * kolom `no_whatsapp` bila berisi nama (bukan nomor), atau
     * dari bagian alamat kabupaten.
     */
    public function kabupatenKotaCetak(): string
    {
        $nilai = trim((string) ($this->no_whatsapp ?? ''));

        if ($nilai !== '' && preg_match('/\p{L}/u', $nilai)) {
            return $nilai;
        }

        return trim((string) ($this->alamat_kabupaten ?? ''));
    }

    /**
     * Kedua jalur kelas dalam satu string, untuk daftar, cetak, dan ekspor.
     *
     * Kalau hanya satu jalur yang terisi, yang itu saja yang ditampilkan supaya
     * tabel tidak penuh sel kosong.
     */
    public function getKelasGabunganAttribute(): string
    {
        $pengajian = trim((string) ($this->kelas_pengajian ?? ''));
        $sekolah = trim((string) ($this->kelas_sekolah ?? ''));

        // Santri yang belum disentuh sejak kolom baru dibuat masih hanya
        // punya nilai di `kelas` yang lama.
        if ($pengajian === '' && $sekolah === '') {
            $lama = trim((string) $this->kelas);

            return $lama !== '' ? $lama : '-';
        }

        if ($pengajian !== '' && $sekolah !== '') {
            return 'Pengajian ' . $pengajian . ' · Sekolah ' . $sekolah;
        }

        return $pengajian !== '' ? 'Pengajian ' . $pengajian : 'Sekolah ' . $sekolah;
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(PresensiSholat::class);
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        if (!$this->no_whatsapp) return null;
        $number = preg_replace('/[^0-9]/', '', $this->no_whatsapp);
        if ($number) {
            if (str_starts_with($number, '62')) {
                $number = '62' . ltrim($number, '620');
            } elseif (str_starts_with($number, '0')) {
                $number = '62' . substr($number, 1);
            }
            return 'https://wa.me/' . $number;
        }
        return null;
    }

    // Daftar bagian alamat, label & contoh format mengikuti form "Tambah Santri".
    // Dipakai sebagai sumber data tunggal oleh filter dan kolom cetak rekap,
    // sehingga format cetak selalu urut mengikuti filter.
    public static function bagianAlamat(): array
    {
        return [
            'jalan' => ['label' => 'Jalan', 'contoh' => 'Contoh: Jln. Raya Sidoarjo'],
            'nomor' => ['label' => 'No. Rumah', 'contoh' => 'Contoh: 45/B'],
            'rt' => ['label' => 'RT', 'contoh' => 'Contoh: 03'],
            'rw' => ['label' => 'RW', 'contoh' => 'Contoh: 01'],
            'dusun' => ['label' => 'Dusun/Kampung', 'contoh' => 'Contoh: Dusun 1'],
            'desa' => ['label' => 'Desa/Kelurahan', 'contoh' => 'Contoh: Desa Sidoarjo'],
            'kecamatan' => ['label' => 'Kecamatan', 'contoh' => 'Contoh: Purmera'],
            'kabupaten' => ['label' => 'Kabupaten/Kota', 'contoh' => 'Contoh: Kota Pasuruan'],
            'provinsi' => ['label' => 'Provinsi', 'contoh' => 'Contoh: Jawa Timur'],
            'kode_pos' => ['label' => 'Kode Pos', 'contoh' => 'Contoh: 61252'],
            'latitude' => ['label' => 'Latitude (GPS)', 'contoh' => 'Contoh: -7.5123456'],
            'longitude' => ['label' => 'Longitude (GPS)', 'contoh' => 'Contoh: 112.3456789'],
            'foto' => ['label' => 'Foto Rumah', 'contoh' => 'JPG, PNG, WEBP · maks. 2MB'],
        ];
    }

    // Rincian alamat per bagian (untuk filter cetak rekap).
    // Kunci => label, contoh, nilai (null = tidak ada data), link (khusus foto).
    public function getAlamatBagianAttribute(): array
    {
        $potong = function ($teks) {
            $teks = trim((string) $teks);
            return $teks === '' ? null : $teks;
        };

        // Nilai ditampilkan polos; label bagian disisipkan saat cetak agar
        // formatcetakan mengikuti filter yang dipilih.
        $nilai = [
            'jalan' => $potong($this->alamat_jalan),
            'nomor' => $potong($this->alamat_nomor_rumah),
            'rt' => $potong($this->alamat_rt),
            'rw' => $potong($this->alamat_rw),
            'dusun' => $potong($this->alamat_dusun),
            'desa' => $potong($this->alamat_desa),
            'kecamatan' => $potong($this->alamat_kecamatan),
            'kabupaten' => $potong($this->alamat_kabupaten),
            'provinsi' => $potong($this->alamat_provinsi),
            'kode_pos' => $potong($this->kode_pos),
            'latitude' => $potong($this->latitude),
            'longitude' => $potong($this->longitude),
            'foto' => $potong($this->foto_rumah) ? basename($this->foto_rumah) : null,
        ];

        $hasil = [];
        foreach (self::bagianAlamat() as $key => $meta) {
            $hasil[$key] = [
                'label' => $meta['label'],
                'contoh' => $meta['contoh'],
                'nilai' => $nilai[$key] ?? null,
                'link' => $key === 'foto' ? $potong($this->foto_rumah) : null,
            ];
        }

        return $hasil;
    }

    public function getAlamatSingkatAttribute(): string
    {
        $parts = array_filter([
            $this->alamat_jalan ? $this->alamat_jalan . ' ' . ($this->alamat_nomor_rumah ?? '') : null,
            $this->alamat_rt ? 'RT ' . $this->alamat_rt : null,
            $this->alamat_rw ? 'RW ' . $this->alamat_rw : null,
            $this->alamat_dusun,
            $this->alamat_desa,
            $this->alamat_kecamatan,
            $this->alamat_kabupaten,
            $this->alamat_provinsi,
            $this->kode_pos,
        ]);
        return trim(implode(', ', $parts));
    }

    // Hitung rating kehadiran (dalam persen)
    public function getRating($days = 30, ?string $endDate = null): float
    {
        $end = $endDate ? \Carbon\Carbon::parse($endDate) : now();
        $startDate = $end->copy()->subDays($days - 1)->format('Y-m-d');
        $endDateStr = $end->format('Y-m-d');
        $totalSeharusnya = 5 * $days; // 5 waktu sholat per hari

        $presensis = $this->presensis()
            ->where('tanggal', '>=', $startDate)
            ->where('tanggal', '<=', $endDateStr)
            ->get();

        $jamaah = $presensis->where('status', 'Jamaah')->count();

        return $totalSeharusnya > 0 ? round(($jamaah / $totalSeharusnya) * 100) : 0;
    }

    // Hitung rating berjamaah berdasarkan waktu sholat tertentu (dalam persen)
    public function getRatingByWaktu(string $waktuSholat, $days = 30, ?string $endDate = null): float
    {
        $end = $endDate ? \Carbon\Carbon::parse($endDate) : now();
        $startDate = $end->copy()->subDays($days - 1)->format('Y-m-d');
        $endDateStr = $end->format('Y-m-d');

        $presensis = $this->presensis()
            ->where('tanggal', '>=', $startDate)
            ->where('tanggal', '<=', $endDateStr)
            ->where('waktu_sholat', $waktuSholat)
            ->get();

        $jamaah = $presensis->where('status', 'Jamaah')->count();
        $masbuq = $presensis->where('status', 'Masbuq')->count();

        $totalDiinput = $presensis->count();

        if ($totalDiinput === 0) {
            return 0;
        }

        $score = $jamaah + ($masbuq * 0.5);
        return round(($score / $totalDiinput) * 100, 1);
    }

    // Detail rating umum (semua waktu) per hari
    public function getRatingDetail($days = 30, ?string $endDate = null): array
    {
        $end = $endDate ? \Carbon\Carbon::parse($endDate) : now();
        $startDate = $end->copy()->subDays($days - 1)->format('Y-m-d');
        $endDateStr = $end->format('Y-m-d');

        $presensis = $this->presensis()
            ->where('tanggal', '>=', $startDate)
            ->where('tanggal', '<=', $endDateStr)
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_sholat', 'asc')
            ->get();

        $detail = [];
        foreach ($presensis as $p) {
            $detail[] = [
                'tanggal' => $p->tanggal->format('d M Y'),
                'status' => $p->status,
                'waktu' => $p->waktu_sholat,
            ];
        }

        return $detail;
    }

    // Detail rating per hari untuk waktu tertentu
    public function getRatingDetailByWaktu(string $waktuSholat, $days = 30, ?string $endDate = null): array
    {
        $end = $endDate ? \Carbon\Carbon::parse($endDate) : now();
        $startDate = $end->copy()->subDays($days - 1)->format('Y-m-d');
        $endDateStr = $end->format('Y-m-d');

        $presensis = $this->presensis()
            ->where('tanggal', '>=', $startDate)
            ->where('tanggal', '<=', $endDateStr)
            ->where('waktu_sholat', $waktuSholat)
            ->orderBy('tanggal', 'asc')
            ->get();

        $detail = [];
        foreach ($presensis as $p) {
            $detail[] = [
                'tanggal' => $p->tanggal->format('d M Y'),
                'status' => $p->status,
            ];
        }

        return $detail;
    }

    // Hitung rating berjamaah untuk semua waktu sholat
    public function getAllWaktuRatings($days = 30, ?string $endDate = null): array
    {
        $waktuList = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $ratings = [];

        foreach ($waktuList as $waktu) {
            $ratings[$waktu] = $this->getRatingByWaktu($waktu, $days, $endDate);
        }

        return $ratings;
    }
}
