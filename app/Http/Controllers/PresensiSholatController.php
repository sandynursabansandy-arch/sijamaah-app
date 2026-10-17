<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\PresensiSholat;
use App\Support\SchemaCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PresensiSholatController extends Controller
{
    /** Peta status database -> label yang dipakai di UI. */
    protected const STATUS_LABEL = [
        'Jamaah' => 'Hadir',
        'Masbuq' => 'Masbuq',
        'Izin'   => 'Izin',
        'Alfa'   => 'Alfa',
    ];

    protected const WAKTU_SHOLAT = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];

    protected ?int $jumlahSantriAktifCache = null;

    protected function jumlahSantriAktif(): int
    {
        if ($this->jumlahSantriAktifCache === null) {
            $this->jumlahSantriAktifCache = max(1, (int) $this->activeSantriQuery()->count());
        }
        return $this->jumlahSantriAktifCache;
    }

    /**
     * Query dasar Santri aktif; otomatis menyesuaikan bila kolom `status` belum ada
     * (instalasi lama / dev) sehingga tidak relies pada information_schema tiap request.
     */
    protected function activeSantriQuery()
    {
        $q = Santri::query();

        if (SchemaCache::hasColumn('santris', 'status')) {
            $q->where('status', 'Aktif');
        }

        return $q;
    }

    /**
     * Agregasi jumlah presensi untuk sekumpulan rentang tanggal dalam SATU query.
     *
     * Sebelumnya tiap grafik memuat SELURUH baris presensi sebagai Eloquent model
     * (ratusan ribu baris untuk dashboard 6 bulan) lalu menghitung di PHP. Itu
     * penyebab utama "502 Bad Gateway": memori PHP habis / request terlalu lama.
     * Sekarang MySQL yang menjumlahkan, sehingga hanya puluhan baris yang dikirim ke PHP.
     *
     * @param  array<int, array{start: string, end: string}>  $buckets
     * @return array<int, array<string, array<string, int>>>  [bucketIndex][waktu]['total'|'hadir'|status]
     */
    protected function aggregatePresensi(array $buckets, ?int $santriId = null, bool $perWaktu = false): array
    {
        if (empty($buckets)) {
            return [];
        }

        $caseParts = [];
        $bindings = [];
        $min = null;
        $max = null;

        foreach (array_values($buckets) as $i => $bucket) {
            $caseParts[] = "WHEN tanggal >= ? AND tanggal <= ? THEN {$i}";
            $bindings[] = $bucket['start'];
            $bindings[] = $bucket['end'];
            $min = $min === null || $bucket['start'] < $min ? $bucket['start'] : $min;
            $max = $max === null || $bucket['end'] > $max ? $bucket['end'] : $max;
        }

        $group = $perWaktu ? 'bucket, waktu_sholat, status' : 'bucket, status';

        // USE INDEX mengarahkan MySQL ke index tanggal. Tanpa itu optimizer
        // cenderung memilih ps_status_tanggal_idx yang tidak selektif, sehingga
        // query jadi efektifnya full table scan.
        // Catatan: ini hanya petunjuk, bukan paksaan — MySQL bebas memakai index
        // lain bila menilainya lebih baik. SQLite tidak mendukung sintaks ini,
        // jadi hanya dipakai di MySQL.
        $useIndex = DB::getDriverName() === 'mysql' ? ' USE INDEX (ps_tanggal_idx)' : '';
        $sql = 'SELECT CASE ' . implode(' ', $caseParts) . ' END AS bucket'
            . ($perWaktu ? ', waktu_sholat' : '')
            . ', status, COUNT(*) AS c'
            . ' FROM presensi_sholats' . $useIndex
            . ' WHERE tanggal >= ? AND tanggal <= ?'
            . ($santriId ? ' AND santri_id = ?' : '')
            . ' GROUP BY ' . $group;

        $bindings[] = $min;
        $bindings[] = $max;
        if ($santriId) {
            $bindings[] = $santriId;
        }

        $out = [];
        foreach (DB::select($sql, $bindings) as $row) {
            $bucketKey = (int) $row->bucket;
            $waktuKey = $perWaktu ? (string) $row->waktu_sholat : '-';
            $count = (int) $row->c;

            $out[$bucketKey][$waktuKey]['total'] = ($out[$bucketKey][$waktuKey]['total'] ?? 0) + $count;
            if ($row->status === 'Jamaah') {
                $out[$bucketKey][$waktuKey]['hadir'] = ($out[$bucketKey][$waktuKey]['hadir'] ?? 0) + $count;
            }
            if (isset(self::STATUS_LABEL[$row->status])) {
                $out[$bucketKey][$waktuKey][$row->status] = ($out[$bucketKey][$waktuKey][$row->status] ?? 0) + $count;
            }
        }

        return $out;
    }

    /**
     * Statistik kehadiran per santri dari SATU query agregat.
     *
     * @param  array<int>  $santriIds
     * @return array<int, array<string, array<string, int>>>  [santri_id][waktu|'*'][status] => jumlah
     */
    protected function presensiStats(array $santriIds, string $start, string $end): array
    {
        if (empty($santriIds) || $start > $end) {
            return [];
        }

        $stats = [];

        $rows = DB::table('presensi_sholats')
            ->select('santri_id', 'waktu_sholat', 'status', DB::raw('COUNT(*) as c'))
            ->where('tanggal', '>=', $start)
            ->where('tanggal', '<=', $end)
            ->whereIn('santri_id', $santriIds)
            ->groupBy('santri_id', 'waktu_sholat', 'status')
            ->get();

        foreach ($rows as $row) {
            $id = (int) $row->santri_id;
            $count = (int) $row->c;
            $status = (string) $row->status;
            $waktu = (string) $row->waktu_sholat;

            $stats[$id]['*'][$status] = ($stats[$id]['*'][$status] ?? 0) + $count;
            $stats[$id][$waktu][$status] = ($stats[$id][$waktu][$status] ?? 0) + $count;
        }

        return $stats;
    }

    /**
     * Slot jadwal = jumlah hari × 5 waktu × jumlah santri.
     * Dipakai sebagai pembagi persentase agar hari yang belum terisi tidak
     * otomatis dianggap tidak hadir.
     */
    protected function slotJadwal(Carbon $start, Carbon $end, ?int $santriId = null, int $perHari = 5): int
    {
        $end = $end->copy();
        if ($end->greaterThan(Carbon::now())) {
            $end = Carbon::now();
        }
        if ($start->greaterThan($end)) {
            return 0;
        }

        $jumlahSantri = $santriId ? 1 : $this->jumlahSantriAktif();

        return (((int) $start->diffInDays($end)) + 1) * $perHari * $jumlahSantri;
    }

    /**
     * Fingerprint status data presensi (jumlah + tanggal terakhir + updated_at terakhir).
     * Dipakai sebagai bagian key cache agar cache otomatis basi saat data berubah.
     */
    protected function presensiVersion(): string
    {
        static $v = null;
        if ($v === null) {
            $row = PresensiSholat::query()
                ->selectRaw('COUNT(*) as c, MAX(tanggal) as mt, MAX(updated_at) as mu')
                ->first();
            $v = ($row->c ?? 0) . '|' . ($row->mt ?? '') . '|' . ($row->mu ?? '');
        }
        return $v;
    }

    public function index(Request $request)
    {
        $latestTanggal = PresensiSholat::max('tanggal') ?? date('Y-m-d');
        $tanggal = $request->get('tanggal', $latestTanggal);
        $waktu = $request->get('waktu_sholat', 'Subuh');
        $kamarId = $request->get('kamar_id', 'all');
        $periodRating = $request->get('period', 7);
        $chartFilter = $request->get('chart_filter', 'minggu');
        $chartStart = $request->get('chart_start');
        $chartEnd = $request->get('chart_end');

        // Navigasi bulan untuk panel "Hadir per Sholat" (hanya admin/musyrif)
        $pwOffset = (int) $request->get('pw', 0);

        $perOrangPeriode = $request->get('per_orang_periode', 'minggu');
        $perOrangStart = $request->get('per_orang_start');
        $perOrangEnd = $request->get('per_orang_end');
        $perOrangWaktu = $request->get('per_orang_waktu', 'all');
        $perOrangKeterangan = $request->get('per_orang_keterangan', 'all');

        $daftarKamar = Kamar::urutRayon(Kamar::all());

        $santriQuery = Santri::query()
            ->where('is_pengajar', true);

        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }

        if (SchemaCache::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery
            ->with(['presensis' => function ($query) use ($tanggal, $waktu) {
                $query->where('tanggal', $tanggal)->where('waktu_sholat', $waktu);
            }])
            ->orderBy('nama', 'asc')
            ->get();

        // Hitung rating (persen) untuk semua santri dari SATU query agregat
        // (sebelumnya N query per santri via getRating()/getRatingByWaktu()).
        $ratingStart = \Carbon\Carbon::parse($tanggal)->subDays($periodRating - 1)->format('Y-m-d');
        $totalSeharusnya = 5 * $periodRating;
        $stats = $this->presensiStats($santris->pluck('id')->all(), $ratingStart, $tanggal);

        $santris = $santris->map(function ($santri) use ($stats, $waktu, $totalSeharusnya) {
            $all = $stats[$santri->id]['*'] ?? [];
            $jamaah = $all['Jamaah'] ?? 0;
            $santri->rating = $totalSeharusnya > 0 ? round(($jamaah / $totalSeharusnya) * 100) : 0;

            $rowsWaktu = $stats[$santri->id][$waktu] ?? [];
            $jamaahW = $rowsWaktu['Jamaah'] ?? 0;
            $masbuqW = $rowsWaktu['Masbuq'] ?? 0;
            $totalW = array_sum($rowsWaktu);
            $score = $jamaahW + ($masbuqW * 0.5);
            $santri->ratingByWaktu = $totalW > 0 ? round(($score / $totalW) * 100, 1) : 0;
            return $santri;
        });

        $isPersonalMode = auth()->check() && !auth()->user()->canManagePresensi();
        $personalSantri = null;
        $scopeSantriId = null;
        if ($isPersonalMode) {
            $personalSantri = Santri::where('nama', auth()->user()->name)->where('status', 'Aktif')->first();
            $scopeSantriId = $personalSantri?->id ?? -1;
        }

        $chartData = $this->chartDataCached($chartFilter, $chartStart, $chartEnd, $scopeSantriId);
        $statusBreakdown = $this->buildStatusBreakdown($tanggal, $waktu, $scopeSantriId);

        // Data panel "Hadir per Sholat" per bulan terpilih (navigasi < >)
        $pwMonth = Carbon::now()->startOfMonth()->addMonths($pwOffset);
        $perWaktu = $this->buildPerWaktuBulan($pwMonth, $scopeSantriId);
        $perWaktuData = $perWaktu['chart'];
        $perWaktuBreakdown = $perWaktu['breakdown'];
        $pwHasData = $perWaktu['hasData'];

        $perOrangFilterNama = null;
        if (auth()->check() && !auth()->user()->isAdmin()) {
            $perOrangFilterNama = auth()->user()->name;
        }
        $perOrangData = $this->buildPerOrangData($kamarId, $perOrangPeriode, $perOrangStart, $perOrangEnd, $perOrangWaktu, $perOrangFilterNama, $perOrangKeterangan, $latestTanggal);

        $topRating = $santris->sortByDesc('rating')->values();

        $personalRating = 0;
        $personalRatingByWaktu = 0;
        $personalRank = null;
        $santriTotal = null;
        if ($isPersonalMode && $personalSantri) {
            $myAll = $stats[$personalSantri->id]['*'] ?? [];
            $personalRating = $totalSeharusnya > 0 ? round((($myAll['Jamaah'] ?? 0) / $totalSeharusnya) * 100) : 0;

            $myWaktu = $stats[$personalSantri->id][$waktu] ?? [];
            $myTotalWaktu = array_sum($myWaktu);
            $myScore = ($myWaktu['Jamaah'] ?? 0) + (($myWaktu['Masbuq'] ?? 0) * 0.5);
            $personalRatingByWaktu = $myTotalWaktu > 0 ? round(($myScore / $myTotalWaktu) * 100, 1) : 0;

            $rankedIds = $this->activeSantriQuery()->pluck('id')->all();
            $jamaahPerSantri = DB::table('presensi_sholats')
                ->select('santri_id', DB::raw('COUNT(*) as c'))
                ->where('tanggal', '>=', $ratingStart)
                ->where('tanggal', '<=', $tanggal)
                ->where('status', 'Jamaah')
                ->whereIn('santri_id', $rankedIds)
                ->groupBy('santri_id')
                ->pluck('c', 'santri_id');

            $ranked = $this->activeSantriQuery()->get()->map(function ($s) use ($jamaahPerSantri, $totalSeharusnya) {
                $s->santriRating = $totalSeharusnya > 0 ? round(((int) ($jamaahPerSantri[$s->id] ?? 0) / $totalSeharusnya) * 100) : 0;
                return $s;
            })->sortByDesc('santriRating')->values();
            $santriTotal = $ranked->count();
            $rankIdx = $ranked->search(fn ($s) => $s->id === $personalSantri->id);
            $personalRank = $rankIdx !== false ? $rankIdx + 1 : null;
        }

        $notifData = $this->buildNotifData($perOrangFilterNama);

        return view('presensi.index', compact(
            'tanggal', 'waktu', 'kamarId', 'daftarKamar', 'santris',
            'periodRating', 'chartData', 'statusBreakdown', 'chartFilter',
            'chartStart', 'chartEnd', 'topRating', 'perOrangData',
            'perOrangPeriode', 'perOrangStart', 'perOrangEnd', 'perOrangWaktu', 'perOrangKeterangan',
            'notifData', 'isPersonalMode', 'personalSantri', 'personalRating', 'personalRatingByWaktu',
            'personalRank', 'santriTotal'
        ) + [
            // 'alfaBeruntun' dihapus: tidak pernah dipakai di view, dan
            // buildNotifData() sudah memanggil getAlfaBeruntun() sendiri.
            // Dua pemanggilan berarti dua query berat (~400 ms) untuk data
            // yang akhirnya dibuang.
            'trenBulanan' => $this->trenBulananCached(6, $scopeSantriId),
            'pwOffset' => $pwOffset,
            'pwMonth' => $pwMonth,
            'perWaktuData' => $perWaktuData,
            'perWaktuBreakdown' => $perWaktuBreakdown,
            'pwHasData' => $pwHasData,
        ]);
    }

    /**
     * Parse tanggal input user dengan format ketat Y-m-d.
     * Return null bila kosong/tidak valid (bukan melempar exception).
     */
    protected function parseTanggal($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Santri dengan alfa beruntun >= $minStreak hari (berdasarkan tanggal terakhir alfa).
     */
    protected function getAlfaBeruntun(int $minStreak = 3): array
    {
        return \App\Services\PresensiAnalysis::alfaBeruntun($minStreak);
    }

    /**
     * Lazy-load daftar nama + tanggal untuk modal detail.
     *
     * Dashboard lama menyertakan daftar nama EVERY status di dalam HTML/JSON awal.
     * Untuk rentang 6 bulan itu ratusan ribu baris -> HTML belasan MB, dan inilah
     * pemicu utama "502 Bad Gateway". Sekarang halaman utama hanya mengirim angka
     * agregat; daftar nama diambil saat modal benar-benar diklik.
     *
     * Query: satu SELECT sempit (tanpa hydrasi model) + join santris.
     */
    public function detail(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Tanggal harus benar-benar Y-m-d. Tanpa validasi, input rusak membuat
        // Carbon melempar InvalidFormatException -> 500.
        $start = $this->parseTanggal($request->query('start'));
        if ($start === null) {
            return response()->json(['error' => 'Format tanggal harus YYYY-MM-DD'], 422);
        }
        $end = $this->parseTanggal($request->query('end')) ?? $start->copy();
        if ($end === null) {
            return response()->json(['error' => 'Format tanggal harus YYYY-MM-DD'], 422);
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }
        if ($start->diffInDays($end) > 400) {
            return response()->json(['error' => 'Rentang tanggal terlalu besar'], 422);
        }

        // Waktu sholat yang tidak dikenal harus DITOLAK, bukan diabaikan diam-diam,
        // kalau tidak caller diam-diam mendapat data satu bulan penuh.
        $waktu = $request->query('waktu');
        if ($waktu !== null && $waktu !== '' && !in_array($waktu, self::WAKTU_SHOLAT, true)) {
            return response()->json(['error' => 'Waktu sholat tidak dikenal'], 422);
        }

        // Santri non-manager hanya boleh melihat datanya sendiri.
        $santriId = null;
        if (!$user->canManagePresensi()) {
            $santriId = Santri::where('nama', $user->name)->value('id');
            if (!$santriId) {
                return response()->json(['names' => []], 200);
            }
        }

        $query = DB::table('presensi_sholats')
            ->join('santris', 'santris.id', '=', 'presensi_sholats.santri_id')
            ->whereBetween('presensi_sholats.tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->when($santriId, fn ($q) => $q->where('presensi_sholats.santri_id', $santriId));

        if ($waktu) {
            $query->where('presensi_sholats.waktu_sholat', $waktu);
        }

        $names = [];
        foreach (self::STATUS_LABEL as $dbStatus => $label) {
            $names[$label] = [];
        }

        // Format wire: { label: { nama: [tanggal, ...] } }.
        // Kalau nama dikirim ulang untuk tiap baris, payload untuk rentang 6 bulan
        // jadi ~3,6x lebih besar (nama adalah bagian terbesar dan berulang).
        $query->select('santris.nama', 'presensi_sholats.tanggal', 'presensi_sholats.status')
            ->orderBy('santris.nama')
            ->chunk(2000, function ($rows) use (&$names) {
                foreach ($rows as $row) {
                    $label = self::STATUS_LABEL[$row->status] ?? null;
                    if ($label === null) {
                        continue;
                    }
                    $names[$label][$row->nama][] = substr((string) $row->tanggal, 0, 10);
                }
            });

        return response()->json(['names' => $names]);
    }

    /**
     * Build notifikasi personal untuk user yang login berdasarkan kehadiran minggu ini.
     */
    public function buildNotifData(?string $filterNama = null): array
    {
        $user = auth()->user();
        if (!$user) return [];

        $nama = $filterNama ?: $user->name;
        $santri = Santri::where('nama', $nama)->first();

        $now = Carbon::now();
        $startWeek = $now->copy()->startOfWeek(Carbon::MONDAY);
        $endWeek = $now->copy()->endOfWeek(Carbon::SUNDAY);
        $startMonth = $now->copy()->startOfMonth();
        $endMonth = $now->copy()->endOfMonth();

        $messages = [];
        $dayIndex = (int) now()->dayOfWeek;

        if ($santri) {
            // Angka mingguan dari SATU query agregat
            // (sebelumnya semua baris minggu ini di-hydrate).
            $weekStats = PresensiSholat::where('santri_id', $santri->id)
                ->whereBetween('tanggal', [$startWeek->format('Y-m-d'), $endWeek->format('Y-m-d')])
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status');

            $totalWeek = (int) $weekStats->sum();
            $hadirWeek = (int) ($weekStats['Jamaah'] ?? 0);
            $alfaWeek = (int) ($weekStats['Alfa'] ?? 0);
            $pctWeek = $totalWeek > 0 ? round(($hadirWeek / $totalWeek) * 100, 1) : 0;

            // Status per tanggal untuk streak alfa. Hanya 2 kolom
            // yang diambil (sebelumnya seluruh kolom baris).
            $monthPresensi = PresensiSholat::where('santri_id', $santri->id)
                ->whereBetween('tanggal', [$startMonth->format('Y-m-d'), $endMonth->format('Y-m-d')])
                ->get(['tanggal', 'status']);

            $monthStats = $monthPresensi->countBy('status');
            $totalMonth = $monthPresensi->count();
            $alfaMonth = (int) ($monthStats['Alfa'] ?? 0);
            $pctMonth = $totalMonth > 0 ? round(((int) ($monthStats['Jamaah'] ?? 0) / $totalMonth) * 100, 1) : 0;

            $alfaStreak = 0;
            $today = $now->copy();
            while (true) {
                $p = $monthPresensi->firstWhere('tanggal', $today->format('Y-m-d'));
                if ($p && $p->status === 'Alfa') {
                    $alfaStreak++;
                    $today->subDay();
                } else {
                    break;
                }
            }

            $praiseTexts = [
                "Mashaa Allah! Kehadiran berjamaahmu minggu ini {$pctWeek}%. Pertahankan semangatmu!",
                "Alhamdulillah, kehadiran berjamaahmu minggu ini {$pctWeek}%! Semoga Allah ridha.",
                "Subhanallah! Kehadiran berjamaahmu {$pctWeek}% minggu ini. Terus istiqamah!",
                "Luar biasa! Kehadiran berjamaahmu minggu ini {$pctWeek}%. Jazakumullah khairan!",
                "Masya Allah, kehadiranmu berjamaah minggu ini {$pctWeek}%! Semoga menjadi amal sholeh.",
                "Allahu Akbar! Kehadiran berjamaahmu minggu ini {$pctWeek}%. Pantang menyerah!",
                "Barakallahu fiik, kehadiran berjamaahmu minggu ini {$pctWeek}%. Semoga diberkahi!",
            ];

            $warningTexts = [
                "Sudah alfa {$alfaStreak} hari berturut-turut. Segera perbaiki dan jangan tinggalkan sholat berjamaah!",
                "Alfa {$alfaStreak} hari berturut! Ingatlah Allah memanggilmu setiap waktu. Yuk bangkit!",
                " Sudah {$alfaStreak} hari alfa beruntun. Sholat berjamaah itu panglima, jangan lewatkan!",
                "Kamu alfa {$alfaStreak} hari berturut. Teman-temanmu menunggumu di masjid!",
                "Alfa beruntun {$alfaStreak} hari! Jangan biarkan Allah menunggumu. Segera perbaiki!",
                "Sudah {$alfaStreak} hari tidak berjamaah. Mulai hari ini, ayo kembali ke masjid!",
                "Peringatan! Alfa {$alfaStreak} hari berturut. Kekuatan ada di sholat berjamaah!",
            ];

            if ($alfaStreak >= 3 || $alfaMonth >= 5 || ($pctWeek < 50 && $totalWeek > 0)) {
                $detail = '';
                if ($alfaStreak >= 3) {
                    $detail = $warningTexts[$dayIndex % count($warningTexts)];
                } elseif ($alfaMonth >= 5) {
                    $detail = "Bulan ini sudah {$alfaMonth} kali alfa. Sholat berjamaah lebih utama dari tidur!";
                    if ($dayIndex % 3 === 1) $detail = "Bulan ini sudah {$alfaMonth} kali alfa. Bangunlah wahai santri, masjid menunggumu!";
                    if ($dayIndex % 3 === 2) $detail = "Bulan ini {$alfaMonth} kali alfa. Jangan sia-siakan waktu muda di jalan Allah!";
                } else {
                    $detail = "Kehadiran berjamaah minggu ini baru {$pctWeek}%. Masjid selalu terbuka untukmu!";
                    if ($dayIndex % 2 === 1) $detail = "Kehadiran berjamaah minggu ini baru {$pctWeek}%. Yuk tingkatkan, Allah pasti membantu!";
                }
                if (!$user->isAdmin()) {
                    $messages[] = [
                        'type' => 'danger',
                        'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                        'title' => 'Peringatan: Kehadiran Buruk',
                        'text' => $detail,
                    ];
                }
            } elseif ($pctWeek >= 75) {
                if (!$user->isAdmin()) {
                    $messages[] = [
                        'type' => 'success',
                        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        'title' => 'Penghargaan: Kehadiran Baik!',
                        'text' => $praiseTexts[$dayIndex % count($praiseTexts)],
                    ];
                }
            }
        }

        if ($user->isAdmin()) {
            $startMonth = $now->copy()->startOfMonth();
            $endMonth = $now->copy()->endOfMonth();
            // Heavy: 30 hari x 5 waktu x semua santri, eager-load + loadMissing = query ganda.
            // Diganti agregasi SQL + join santris, hasilnya hanya maksimal 5 baris per santri.
            $topAlfa = DB::table('presensi_sholats as p')
                ->join('santris as s', 's.id', '=', 'p.santri_id')
                ->leftJoin('kamars as k', 'k.id', '=', 's.kamar_id')
                ->whereBetween('p.tanggal', [$startMonth->format('Y-m-d'), $endMonth->format('Y-m-d')])
                ->where('p.status', 'Alfa')
                ->groupBy('p.santri_id', 's.nama', 'k.nama_kamar')
                ->havingRaw('COUNT(*) >= 3')
                ->orderByDesc('c')
                ->limit(5)
                ->selectRaw('s.nama as nama, k.nama_kamar as kamar, COUNT(*) as c')
                ->get();

            if ($topAlfa->isNotEmpty()) {
                $list = $topAlfa->map(fn ($a) => "<strong>{$a->nama}</strong> (" . ($a->kamar ?? '-') . ") &mdash; {$a->c} kali")->implode(', ');
                $messages[] = [
                    'type' => 'danger',
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                    'title' => 'Admin: Santri Sering Alfa Bulan Ini',
                    'text' => "{$list}. Segera hubungi pengasuh masing-masing!",
                ];
            }

            $alfaStreakList = array_slice($this->getAlfaBeruntun(3), 0, 5);

            if (count($alfaStreakList) > 0) {
                $listStr = array_map(fn($a) => "<strong>{$a['nama']}</strong> ({$a['kamar']}) &mdash; {$a['streak']} hari berturut", $alfaStreakList);
                $messages[] = [
                    'type' => 'danger',
                    'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'title' => 'Admin: Santri Alfa Berturut-turut',
                    'text' => implode(', ', $listStr) . '. Segera tindak lanjuti!',
                ];
            }
        }

        return $messages;
    }

    /**
     * Persentase kehadiran (Jamaah / slot jadwal) per bulan, 6 bulan terakhir.
     *
     * Rincian nama per status TIDAK lagi disertakan: jumlahnya ratusan ribu baris
     * dan membuat HTML dashboard berukuran belasan MB (penyebab 502). Rincian
     * diambil on-demand lewat endpoint `detail()` saat modal diklik.
     */
    protected function getTrenBulanan(int $jumlahBulan = 6, ?int $santriId = null): array
    {
        $months = [];
        for ($i = $jumlahBulan - 1; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $months[] = $m;
        }

        $buckets = [];
        foreach ($months as $m) {
            $buckets[] = ['start' => $m->copy()->startOfMonth()->format('Y-m-d'), 'end' => $m->copy()->endOfMonth()->format('Y-m-d')];
        }

        $agg = $this->aggregatePresensi($buckets, $santriId);

        $tren = [];
        foreach ($months as $i => $m) {
            $row = $agg[$i]['-'] ?? [];
            $total = (int) ($row['total'] ?? 0);
            $hadir = (int) ($row['hadir'] ?? 0);

            $slotAkhir = $m->format('Y-m') === Carbon::now()->format('Y-m')
                ? Carbon::now()->copy()
                : $m->copy()->endOfMonth();
            $slotJadwal = $this->slotJadwal($m->copy()->startOfMonth(), $slotAkhir, $santriId);

            $tren[] = [
                'label' => $m->translatedFormat('M'),
                'pct' => $slotJadwal > 0 ? round(($hadir / $slotJadwal) * 100) : 0,
                'detail' => [
                    'label' => $m->translatedFormat('F Y'),
                    'start' => $buckets[$i]['start'],
                    'end' => $slotAkhir->format('Y-m-d'),
                    'total' => $total,
                    'hadir' => $hadir,
                ],
            ];
        }

        return $tren;
    }

    /**
     * buildChartData() yang di-cache. Data basi otomatis saat presensi berubah (via presensiVersion).
     */
    protected function chartDataCached(string $filter, ?string $startDate, ?string $endDate, ?int $santriId): array
    {
        // Hanya cache untuk filter yang memang berat (bulan penuh / setahun penuh).
        // Untuk minggu/hari yang sudah ~0,1s, memakai cache hanya menambah query overhead.
        if (!in_array($filter, ['bulan', 'tahun'], true)) {
            return $this->buildChartData($filter, $startDate, $endDate, $santriId);
        }
        return \Illuminate\Support\Facades\Cache::remember(
            'presensi.chart.' . $filter . '.' . ($startDate ?? 'x') . '.' . ($endDate ?? 'x') . '.' . ($santriId ?? 'all') . '.' . $this->presensiVersion(),
            300,
            fn () => $this->buildChartData($filter, $startDate, $endDate, $santriId)
        );
    }

    /**
     * getTrenBulanan() yang di-cache.
     */
    protected function trenBulananCached(int $jumlahBulan = 6, ?int $santriId = null): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'dash.tren.' . $jumlahBulan . '.' . ($santriId ?? 'all') . '.' . $this->presensiVersion(),
            300,
            fn () => $this->getTrenBulanan($jumlahBulan, $santriId)
        );
    }

    protected function buildChartData(string $filter = 'minggu', ?string $startDate = null, ?string $endDate = null, ?int $santriId = null): array
    {
        $chartData = match ($filter) {
            'hari' => $this->buildHarianData($startDate, $santriId),
            'minggu' => $this->buildMingguanData($startDate, $endDate, $santriId),
            'bulan' => $this->buildBulananData($startDate, $endDate, $santriId),
            'tahun' => $this->buildTahunanData($santriId),
            default => $this->buildMingguanData($startDate, $endDate, $santriId),
        };

        $rangeStart = $chartData['dateRange']['start'];
        $rangeEnd = $chartData['dateRange']['end'];

        $waktuSholatLabels = self::WAKTU_SHOLAT;
        $presensiPerWaktu = [];
        $waktuBreakdownDetail = [];

        // SATU query agregat (sebelumnya: memuat seluruh baris range sebagai Eloquent
        // model + daftar nama per status, ratusan ribu baris untuk filter bulan/tahun).
        $perWaktuAgg = $this->aggregatePresensi(
            [['start' => $rangeStart, 'end' => $rangeEnd]],
            $santriId,
            true
        )[0] ?? [];

        $slotJadwal = $this->slotJadwal(Carbon::parse($rangeStart), Carbon::parse($rangeEnd), $santriId, 1);
        $statusKehadiran = ['Hadir' => 0, 'Masbuq' => 0, 'Izin' => 0, 'Alfa' => 0];

        foreach ($waktuSholatLabels as $waktuLabel) {
            $row = $perWaktuAgg[$waktuLabel] ?? [];
            $total = (int) ($row['total'] ?? 0);
            $jamaah = (int) ($row['Jamaah'] ?? 0);
            $percentage = $slotJadwal > 0 ? round(($jamaah / $slotJadwal) * 100) : 0;

            $presensiPerWaktu[] = $percentage;

            $statusKehadiran['Hadir'] += $jamaah;
            $statusKehadiran['Masbuq'] += (int) ($row['Masbuq'] ?? 0);
            $statusKehadiran['Izin'] += (int) ($row['Izin'] ?? 0);
            $statusKehadiran['Alfa'] += (int) ($row['Alfa'] ?? 0);

            $waktuBreakdownDetail[$waktuLabel] = [
                'total' => $total,
                'jamaah' => $jamaah,
                'masbuq' => (int) ($row['Masbuq'] ?? 0),
                'izin' => (int) ($row['Izin'] ?? 0),
                'alfa' => (int) ($row['Alfa'] ?? 0),
                'percentage' => $percentage,
                'start' => $rangeStart,
                'end' => $rangeEnd,
            ];
        }

        $chartData['presensiPerWaktu'] = [
            'labels' => $waktuSholatLabels,
            'values' => $presensiPerWaktu,
            'subtitle' => Carbon::parse($rangeStart)->translatedFormat('d M') . ' - ' . Carbon::parse($rangeEnd)->translatedFormat('d M Y'),
        ];
        $chartData['waktuBreakdownDetail'] = $waktuBreakdownDetail;
        $chartData['statusKehadiran'] = $statusKehadiran;

        return $chartData;
    }

    /**
     * Persentase kehadiran per waktu sholat untuk satu bulan penuh (panel "Hadir per Sholat" admin).
     * Bulan berjalan dibatasi sampai hari ini. Bulan tanpa data -> hasData = false.
     */
    protected function buildPerWaktuBulan(Carbon $month, ?int $santriId = null): array
    {
        $monthStart = $month->copy()->startOfMonth();
        $isCurrent = $monthStart->format('Y-m') === Carbon::now()->format('Y-m');
        $monthEnd = $isCurrent ? Carbon::now()->copy() : $month->copy()->endOfMonth();

        $rangeStart = $monthStart->format('Y-m-d');
        $rangeEnd = $monthEnd->format('Y-m-d');

        $waktuLabels = self::WAKTU_SHOLAT;
        $values = [];
        $breakdown = [];
        $slotJadwal = $this->slotJadwal($monthStart, $monthEnd, $santriId, 1);

        $perWaktuAgg = $this->aggregatePresensi(
            [['start' => $rangeStart, 'end' => $rangeEnd]],
            $santriId,
            true
        )[0] ?? [];

        $totalRecord = 0;
        foreach ($waktuLabels as $wl) {
            $row = $perWaktuAgg[$wl] ?? [];
            $total = (int) ($row['total'] ?? 0);
            $totalRecord += $total;
            $jamaah = (int) ($row['Jamaah'] ?? 0);
            $percentage = $slotJadwal > 0 ? round(($jamaah / $slotJadwal) * 100) : 0;
            $values[] = $percentage;

            $breakdown[$wl] = [
                'total' => $total,
                'jamaah' => $jamaah,
                'masbuq' => (int) ($row['Masbuq'] ?? 0),
                'izin' => (int) ($row['Izin'] ?? 0),
                'alfa' => (int) ($row['Alfa'] ?? 0),
                'percentage' => $percentage,
                'start' => $rangeStart,
                'end' => $rangeEnd,
            ];
        }

        return [
            'chart' => [
                'labels' => $waktuLabels,
                'values' => $values,
                'subtitle' => $monthStart->translatedFormat('F Y') . ($isCurrent ? ' (s/d hari ini)' : ''),
            ],
            'breakdown' => $breakdown,
            'hasData' => $totalRecord > 0,
        ];
    }

    protected function buildMingguanData(?string $startDate = null, ?string $endDate = null, ?int $santriId = null): array
    {
        $target = Carbon::parse($endDate ?: $startDate ?: now())->startOfMonth();
        $monthStart = $target->copy();
        $monthEnd = $target->copy()->endOfMonth();

        $labels = [];
        $detailLabels = [];
        $values = [];
        $detailData = [];

        $daysInMonth = (int) $monthEnd->format('d');
        $base = intdiv($daysInMonth, 4);
        $rem = $daysInMonth % 4;
        $segStart = null;
        $prevEnd = null;
        $segments = [];

        for ($i = 0; $i < 4; $i++) {
            $segLen = $base + ($i < $rem ? 1 : 0);
            $segStart = $i === 0 ? $monthStart->copy() : $prevEnd->copy()->addDay();
            $segEnd = $segStart->copy()->addDays($segLen - 1);

            $segments[] = ['start' => $segStart, 'end' => $segEnd];
            $prevEnd = $segEnd->copy();
        }

        // SATU query untuk keempat segmen (sebelumnya 4 aggregate + 4 query nama = 8 query
        // yang masing-masing memuat semua baris segmen sebagai Eloquent model).
        $agg = $this->aggregatePresensi(array_map(fn ($s) => [
            'start' => $s['start']->format('Y-m-d'),
            'end' => $s['end']->format('Y-m-d'),
        ], $segments), $santriId);

        foreach ($segments as $i => $segment) {
            $segStart = $segment['start'];
            $segEnd = $segment['end'];
            $row = $agg[$i]['-'] ?? [];
            $total = (int) ($row['total'] ?? 0);
            $hadir = (int) ($row['hadir'] ?? 0);

            $labels[] = 'Minggu ' . ($i + 1);
            $detailLabels[] = $segStart->translatedFormat('d M') . ' - ' . $segEnd->translatedFormat('d M Y');

            $slotJadwal = $this->slotJadwal($segStart, $segEnd, $santriId);
            $values[] = $slotJadwal > 0 ? round(($hadir / $slotJadwal) * 100) : 0;

            $detailData[] = [
                'label' => 'Minggu ' . ($i + 1) . ' (' . $segStart->translatedFormat('d M') . ' - ' . $segEnd->translatedFormat('d M Y') . ')',
                'start' => $segStart->format('Y-m-d'),
                'end' => $segEnd->format('Y-m-d'),
                'total' => $total,
                'hadir' => $hadir,
            ];
        }

        return [
            'presensiBulanan' => [
                'labels' => $labels,
                'detailLabels' => $detailLabels,
                'values' => $values,
                'detailData' => $detailData,
                'title' => 'Kehadiran Per Minggu',
                'subtitle' => $monthStart->translatedFormat('M Y'),
            ],
            'dateRange' => [
                'start' => $monthStart->format('Y-m-d'),
                'end' => $monthEnd->format('Y-m-d'),
            ],
        ];
    }

    protected function buildHarianData(?string $date = null, ?int $santriId = null): array
    {
        $ref = $date ? Carbon::parse($date) : Carbon::now();
        $start = $ref->copy()->startOfWeek(Carbon::MONDAY);
        $end = $ref->copy()->endOfWeek(Carbon::SUNDAY);

        $labels = [];
        $detailLabels = [];
        $values = [];
        $detailData = [];

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }

        // SATU query untuk 7 hari (sebelumnya 7 aggregate + 7 query nama).
        $agg = $this->aggregatePresensi(array_map(fn ($d) => [
            'start' => $d->format('Y-m-d'),
            'end' => $d->format('Y-m-d'),
        ], $days), $santriId);

        foreach ($days as $i => $date) {
            $dateStr = $date->format('Y-m-d');
            $row = $agg[$i]['-'] ?? [];
            $total = (int) ($row['total'] ?? 0);
            $hadir = (int) ($row['hadir'] ?? 0);

            $labels[] = $date->translatedFormat('D');
            $detailLabels[] = $date->translatedFormat('d M Y');

            // Slot jadwal = 5 waktu per hari × jumlah santri; 0 untuk hari yang belum tiba
            $slotJadwal = $date->lte(Carbon::now()) ? 5 * ($santriId ? 1 : $this->jumlahSantriAktif()) : 0;
            $values[] = $slotJadwal > 0 ? round(($hadir / $slotJadwal) * 100) : 0;

            $detailData[] = [
                'label' => $date->translatedFormat('l, d M Y'),
                'start' => $dateStr,
                'end' => $dateStr,
                'total' => $total,
                'hadir' => $hadir,
            ];
        }

        return [
            'presensiBulanan' => [
                'labels' => $labels,
                'detailLabels' => $detailLabels,
                'values' => $values,
                'detailData' => $detailData,
                'title' => 'Kehadiran Per Hari',
                'subtitle' => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            ],
            'dateRange' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
        ];
    }

    protected function buildBulananData(?string $startMonth = null, ?string $endMonth = null, ?int $santriId = null): array
    {
        $end = $endMonth ? Carbon::parse($endMonth)->endOfMonth() : Carbon::now();
        $start = $startMonth ? Carbon::parse($startMonth)->startOfMonth() : $end->copy()->subMonths(5)->startOfMonth();

        $labels = [];
        $detailLabels = [];
        $values = [];
        $detailData = [];

        $months = (int) $start->diffInMonths($end) + 1;
        $months = min($months, 24);

        $buckets = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $buckets[] = [
                'start' => $month->copy()->startOfMonth()->format('Y-m-d'),
                'end' => $month->copy()->endOfMonth()->format('Y-m-d'),
            ];
        }

        // SATU query untuk seluruh bulan yang dipilih (sebelumnya 2 query per bulan,
        // masing-masing memuat semua baris bulan + daftar nama).
        $agg = $this->aggregatePresensi($buckets, $santriId);

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $row = $agg[$i]['-'] ?? [];
            $total = (int) ($row['total'] ?? 0);
            $hadir = (int) ($row['hadir'] ?? 0);

            $labels[] = $month->translatedFormat('M Y');
            $detailLabels[] = $month->translatedFormat('M Y');

            $slotAkhir = $month->format('Y-m') === Carbon::now()->format('Y-m')
                ? Carbon::now()->copy()
                : $month->copy()->endOfMonth();
            $slotJadwal = $this->slotJadwal($month->copy()->startOfMonth(), $slotAkhir, $santriId);
            $values[] = $slotJadwal > 0 ? round(($hadir / $slotJadwal) * 100) : 0;

            $detailData[] = [
                'label' => $month->translatedFormat('F Y'),
                'start' => $buckets[$i]['start'],
                'end' => $buckets[$i]['end'],
                'total' => $total,
                'hadir' => $hadir,
            ];
        }

        return [
            'presensiBulanan' => [
                'labels' => $labels,
                'detailLabels' => $detailLabels,
                'values' => $values,
                'detailData' => $detailData,
                'title' => 'Kehadiran Per Bulan',
                'subtitle' => $start->translatedFormat('M Y') . ' - ' . $end->translatedFormat('M Y'),
            ],
            'dateRange' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
        ];
    }

    protected function buildTahunanData(?int $santriId = null): array
    {
        $labels = [];
        $detailLabels = [];
        $values = [];
        $detailData = [];

        $years = [];
        for ($i = 2; $i >= 0; $i--) {
            $years[] = now()->subYears($i);
        }

        // SATU query untuk tiga tahun (sebelumnya 2 query per tahun; filter "tahun"
        // sebelumnya memuat SELURUH baris 3 tahun sekaligus sebagai Eloquent model).
        $agg = $this->aggregatePresensi(array_map(fn ($y) => [
            'start' => $y->copy()->startOfYear()->format('Y-m-d'),
            'end' => $y->copy()->endOfYear()->format('Y-m-d'),
        ], $years), $santriId);

        foreach ($years as $i => $year) {
            $yearLabel = $year->translatedFormat('Y');
            $row = $agg[$i]['-'] ?? [];
            $total = (int) ($row['total'] ?? 0);
            $hadir = (int) ($row['hadir'] ?? 0);

            $labels[] = $yearLabel;
            $detailLabels[] = $yearLabel;

            $slotAkhir = $year->format('Y') === Carbon::now()->format('Y')
                ? Carbon::now()->copy()
                : $year->copy()->endOfYear();
            $slotJadwal = $this->slotJadwal($year->copy()->startOfYear(), $slotAkhir, $santriId);
            $values[] = $slotJadwal > 0 ? round(($hadir / $slotJadwal) * 100) : 0;

            $detailData[] = [
                'label' => $yearLabel,
                'start' => $year->copy()->startOfYear()->format('Y-m-d'),
                'end' => $year->copy()->endOfYear()->format('Y-m-d'),
                'total' => $total,
                'hadir' => $hadir,
            ];
        }

        return [
            'presensiBulanan' => [
                'labels' => $labels,
                'detailLabels' => $detailLabels,
                'values' => $values,
                'detailData' => $detailData,
                'title' => 'Kehadiran 3 Tahun Terakhir',
                'subtitle' => 'Per tahun',
            ],
            'dateRange' => [
                'start' => now()->subYears(2)->startOfYear()->format('Y-m-d'),
                'end' => now()->endOfYear()->format('Y-m-d'),
            ],
        ];
    }

    protected function buildStatusBreakdown(string $tanggal, string $waktu, ?int $santriId = null): array
    {
        $statuses = ['Jamaah', 'Masbuq', 'Izin', 'Alfa'];

        // SATU query untuk semua status (sebelumnya 4 query terpisah + 8 query
        // eager-load santri/kamar per status). Eager-load dijalankan sekali untuk
        // seluruh baris, lalu difilter per status di PHP.
        $rows = PresensiSholat::where('tanggal', $tanggal)
            ->where('waktu_sholat', $waktu)
            ->whereIn('status', $statuses)
            ->when($santriId, fn ($q) => $q->where('santri_id', $santriId))
            ->with(['santri:id,nama,nis,kamar_id,jabatan', 'santri.kamar:id,nama_kamar'])
            ->get();

        $breakdown = [];
        foreach ($statuses as $status) {
            // ->values() wajib: ->where() mempertahankan key asli (0,1,4,5,...)
            // sehingga json_encode menghasilkan OBJEK, bukan ARRAY.
            // JS (copyStatusChartToClipboard, showStatusDetail, updateStatusCounts)
            // memakai santris.length / santris.forEach / santris.map yang hanya
            // ada di array.
            $breakdown[$status === 'Jamaah' ? 'Hadir' : $status] = $rows
                ->where('status', $status)
                ->map(function ($presensi) {
                    return [
                        'nama' => $presensi->santri?->nama ?? 'Unknown',
                        'nis' => $presensi->santri?->nis ?? '-',
                        'kamar' => $presensi->santri?->kamar?->nama_kamar ?? '-',
                        'jabatan' => $presensi->santri?->jabatan ?? '-',
                    ];
                })
                ->values();
        }

        return $breakdown;
    }

    protected function buildPerOrangData(?string $kamarId, string $periode = 'minggu', ?string $startDate = null, ?string $endDate = null, ?string $waktuSholat = null, ?string $filterNama = null, ?string $filterKeterangan = null, ?string $latestDate = null): array
    {
        // Tanggal terakhir sudah dihitung di index(); bila tidak dikirim
        // (pemanggil lain), hitung sendiri agar tidak ada query ganda.
        $latestDate = $latestDate ?? (PresensiSholat::max('tanggal') ?? date('Y-m-d'));
        $ref = Carbon::parse($latestDate);
        switch ($periode) {
            case 'bulan':
                $start = $endDate ? Carbon::parse($endDate)->startOfMonth() : $ref->copy()->startOfMonth();
                $end = $endDate ? Carbon::parse($endDate)->endOfMonth() : $ref->copy()->endOfMonth();
                break;
            case 'tanggal':
                $start = $startDate ? Carbon::parse($startDate) : $ref->copy()->startOfWeek(Carbon::MONDAY);
                $end = $endDate ? Carbon::parse($endDate) : $ref->copy()->endOfWeek(Carbon::SUNDAY);
                break;
            default: // minggu
                $start = $startDate ? Carbon::parse($startDate) : $ref->copy()->startOfWeek(Carbon::MONDAY);
                $end = $endDate ? Carbon::parse($endDate) : $ref->copy()->endOfWeek(Carbon::SUNDAY);
                break;
        }

        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        $allWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $waktuList = $waktuSholat && $waktuSholat !== 'all' ? [$waktuSholat] : $allWaktu;

        $santriQuery = Santri::query()
            ->where('is_pengajar', true);
        $kamarIds = ($kamarId === 'all' || $kamarId === '' || $kamarId === null)
            ? []
            : array_map('trim', explode(',', $kamarId));
        if (count($kamarIds) > 0) {
            $santriQuery->whereIn('kamar_id', $kamarIds);
        }
        if (SchemaCache::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }
        if ($filterNama) {
            $santriQuery->where('nama', $filterNama);
        }

        $santris = $santriQuery
            ->with(['kamar:id,nama_kamar'])
            ->orderBy('nama', 'asc')
            ->get();

        $days = (int) $start->diffInDays($end) + 1;
        $totalJadwal = $days * count($waktuList);

        // Angka kehadiran diambil dari SATU query agregat, bukan eager-load.
        // Versi lama memakai with(['presensis' => ...]) yang menghydrasi tiap baris
        // presensi menjadi objek Eloquent: 300 santri x 30 hari x 5 waktu =
        // 45.000 objek (~70 MB), padahal view hanya butuh 4 angka per santri.
        // Pemakaian memori berlebihan itulah pemicu "502 Bad Gateway" di hosting.
        $jumlahPerSantri = [];
        if ($santris->isNotEmpty()) {
            $agg = DB::table('presensi_sholats')
                ->select('santri_id', 'status', DB::raw('COUNT(*) as c'))
                ->whereIn('santri_id', $santris->pluck('id')->all())
                ->where('tanggal', '>=', $startStr)
                ->where('tanggal', '<=', $endStr)
                ->whereIn('waktu_sholat', $waktuList)
                ->groupBy('santri_id', 'status')
                ->get();

            foreach ($agg as $row) {
                $jumlahPerSantri[(int) $row->santri_id][(string) $row->status] = (int) $row->c;
            }
        }

        $perOrang = $santris->map(function ($santri) use ($totalJadwal, $filterKeterangan, $jumlahPerSantri) {
            $rows = $jumlahPerSantri[$santri->id] ?? [];
            $hadir = (int) ($rows['Jamaah'] ?? 0);
            $masbuq = (int) ($rows['Masbuq'] ?? 0);
            $izin = (int) ($rows['Izin'] ?? 0);
            $alfa = (int) ($rows['Alfa'] ?? 0);
            $totalIsi = $hadir + $masbuq + $izin + $alfa;
            if ($filterKeterangan && $filterKeterangan !== 'all') {
                $filteredCount = match($filterKeterangan) {
                    'Jamaah' => $hadir, 'Masbuq' => $masbuq, 'Izin' => $izin, 'Alfa' => $alfa, default => 0,
                };
                $persentase = $totalJadwal > 0 ? round(($filteredCount / $totalJadwal) * 100) : 0;
            } else {
                $persentase = $totalJadwal > 0 ? round(($hadir / $totalJadwal) * 100) : 0;
            }

            return [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'nis' => $santri->nis,
                'kamar' => $santri->kamar?->nama_kamar ?? '-',
                'jabatan' => $santri->jabatan ?? '-',
                'hadir' => $hadir,
                'masbuq' => $masbuq,
                'izin' => $izin,
                'alfa' => $alfa,
                'total' => $totalIsi,
                'persentase' => $persentase,
            ];
        });

        if ($filterKeterangan && $filterKeterangan !== 'all' && !$filterNama) {
            $sortField = match ($filterKeterangan) {
                'Jamaah' => 'hadir',
                'Masbuq' => 'masbuq',
                'Izin'   => 'izin',
                'Alfa'   => 'alfa',
                default  => 'hadir',
            };
            $perOrang = $perOrang->sortByDesc($sortField);
        } elseif ($filterNama) {
            $perOrang = $perOrang->sortBy('nama');
        } else {
            $perOrang = $perOrang->sortByDesc('persentase')->sortByDesc('hadir');
        }
        $perOrang = $perOrang->values();

        return [
            'data' => $perOrang,
            'totalJadwal' => $totalJadwal,
            'dateRange' => [
                'start' => $start->format('d M Y'),
                'end' => $end->format('d M Y'),
            ],
            'periodeLabel' => $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d M Y'),
            'isSelf' => (bool) $filterNama,
        ];
    }

    public function rekap(Request $request)
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $waktu = $request->get('waktu_sholat', 'Subuh');
        $kamarId = $request->get('kamar_id', 'all');
        $periodRating = $request->get('period', 7);

        $daftarKamar = Kamar::urutRayon(Kamar::all());

        $santriQuery = Santri::query()
            ->where('is_pengajar', true);

        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }

        if (SchemaCache::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery
            ->with(['presensis' => function ($query) use ($tanggal, $waktu) {
                $query->where('tanggal', $tanggal)->where('waktu_sholat', $waktu);
            }])
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function ($santri) use ($periodRating, $waktu, $tanggal) {
                $santri->rating = $santri->getRating($periodRating, $tanggal);
                $santri->ratingByWaktu = $santri->getRatingByWaktu($waktu, $periodRating, $tanggal);
                return $santri;
            });

        return view('presensi.rekap', compact(
            'tanggal', 'waktu', 'kamarId', 'daftarKamar', 'santris', 'periodRating'
        ));
    }

    public function cetakRekap(Request $request)
    {
        $periode      = $request->get('periode', 'mingguan');
        $tanggalRef   = $request->get('tanggal', date('Y-m-d'));
        $kamarId      = $this->normalizeRayonFilter($request->get('kamar_ids', $request->get('kamar_id', 'all')));
        $filterWaktu  = $request->get('filter_waktu', 'all');
        $filterStatus = $request->get('filter_status', '');
        $mode         = $request->get('mode', 'absensi');

        // Hanya status yang valid yang masuk mode filter; nilai lain dianggap
        // "Semua Status" agar tampilan halaman selalu sinkron dengan data.
        if (!in_array($filterStatus, ['alfa', 'masbuq', 'izin'])) {
            $filterStatus = '';
        }

        // Support multi-waktu: "Maghrib,Subuh" atau array (checkbox)
        $semuaWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $filterWaktu = $this->normalizeWaktuFilter($filterWaktu);
        $waktuList  = $filterWaktu !== 'all' ? explode(',', $filterWaktu) : $semuaWaktu;
        $filterWaktuParam = $filterWaktu;

        $ref = Carbon::parse($tanggalRef);

        switch ($periode) {
            case 'bulanan':
                $tanggalMulai    = $ref->copy()->startOfMonth();
                $tanggalSelesai  = $ref->copy()->endOfMonth();
                $periodeLabel    = $ref->translatedFormat('F Y');
                break;
            case 'tahunan':
                $tanggalMulai    = $ref->copy()->startOfYear();
                $tanggalSelesai  = $ref->copy()->endOfYear();
                $periodeLabel    = $ref->translatedFormat('Y');
                break;
            default:
                $tanggalMulai    = $ref->copy()->startOfWeek(Carbon::MONDAY);
                $tanggalSelesai  = $ref->copy()->endOfWeek(Carbon::SUNDAY);
                $periodeLabel    = $tanggalMulai->isoFormat('D MMM') . ' – ' . $tanggalSelesai->isoFormat('D MMM YYYY');
                break;
        }

        $daftarKamar = Kamar::urutRayon(Kamar::all());

        $allDates = [];
        $temp = $tanggalMulai->copy();
        while ($temp->lte($tanggalSelesai)) {
            $allDates[] = $temp->copy();
            $temp->addDay();
        }

        $santriQuery = Santri::query()
            ->where('is_pengajar', true);
        $kamarIdsArr = ($kamarId === 'all' || $kamarId === '' || $kamarId === null)
            ? []
            : array_map('trim', explode(',', $kamarId));
        if (count($kamarIdsArr) > 0) {
            $santriQuery->whereIn('kamar_id', $kamarIdsArr);
        }
        if (SchemaCache::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery
            ->with(['presensis' => function ($q) use ($tanggalMulai, $tanggalSelesai, $waktuList) {
                $q->where('tanggal', '>=', $tanggalMulai->format('Y-m-d'))
                  ->where('tanggal', '<=', $tanggalSelesai->format('Y-m-d'))
                  ->whereIn('waktu_sholat', $waktuList);
            }])
            ->orderBy('nama', 'asc')
            ->get();

        $rekapData = [];
        $totalHadir  = 0;
        $totalMasbuq = 0;
        $totalIzin   = 0;
        $totalAlfa   = 0;

        foreach ($santris as $santri) {
            $presensiKeyed = $santri->presensis->keyBy(
                fn($p) => $p->tanggal->format('Y-m-d') . '|' . $p->waktu_sholat
            );

            $hariDetail = [];
            foreach ($allDates as $date) {
                $dateStr  = $date->format('Y-m-d');
                $statuses = [];
                foreach ($waktuList as $waktu) {
                    $presensi = $presensiKeyed->get($dateStr . '|' . $waktu);
                    $statuses[$waktu] = $presensi ? $presensi->status : '-';
                }
                $hariDetail[] = [
                    'date'     => $dateStr,
                    'label'    => $date->isoFormat('ddd, D MMM'),
                    'statuses' => $statuses,
                ];
            }

            $hadir  = 0;
            $masbuq = 0;
            $izin   = 0;
            $alfa   = 0;
            foreach ($hariDetail as $hari) {
                foreach ($hari['statuses'] as $st) {
                    match ($st) {
                        'Jamaah' => $hadir++,
                        'Masbuq' => $masbuq++,
                        'Izin'   => $izin++,
                        'Alfa'   => $alfa++,
                        default  => null,
                    };
                }
            }

            $totalDiisi = $hadir + $masbuq + $izin + $alfa;
            $persentase = $totalDiisi > 0
                ? (int) round(($hadir / $totalDiisi) * 100)
                : 0;

            $rekapData[] = [
                'nama'       => $santri->nama,
                'jabatan'    => $santri->jabatan,
                'nis'        => $santri->nis,
                'hadir'      => $hadir,
                'masbuq'     => $masbuq,
                'izin'       => $izin,
                'alfa'       => $alfa,
                'total'      => $totalDiisi,
                'persentase' => $persentase,
                'hariDetail' => $hariDetail,
            ];

            $totalHadir  += $hadir;
            $totalMasbuq += $masbuq;
            $totalIzin   += $izin;
            $totalAlfa   += $alfa;
        }

        // Filter by status: only show santri with ≥1 of that status
        if (in_array($filterStatus, ['alfa', 'masbuq', 'izin'])) {
            $statusKey = $filterStatus;
            $rekapData = array_values(array_filter($rekapData, fn($r) => $r[$statusKey] > 0));
            $totalHadir  = 0;
            $totalMasbuq = 0;
            $totalIzin   = 0;
            $totalAlfa   = 0;
            foreach ($rekapData as $r) {
                $totalHadir  += $r['hadir'];
                $totalMasbuq += $r['masbuq'];
                $totalIzin   += $r['izin'];
                $totalAlfa   += $r['alfa'];
            }
        }

        $totalDiisiAll = $totalHadir + $totalMasbuq + $totalIzin + $totalAlfa;
        $persentaseUmum = $totalDiisiAll > 0
            ? (int) round(($totalHadir / $totalDiisiAll) * 100)
            : 0;

        $kamarNamaList = [];
        if ($kamarId !== 'all') {
            foreach (explode(',', $kamarId) as $kid) {
                $k = $daftarKamar->firstWhere('id', (int)$kid);
                if ($k) $kamarNamaList[] = $k->nama_kamar;
            }
        }
        $kamarLabel = count($kamarNamaList) > 0 ? implode(', ', $kamarNamaList) : 'Semua Rayon';

        $statusLabels = [
            'alfa'   => 'Alfa',
            'masbuq' => 'Masbuq',
            'izin'   => 'Izin',
        ];

        return view($mode === 'pdf' ? 'presensi.rekap-cetak-pdf' : 'presensi.rekap-cetak', compact(
            'periode', 'periodeLabel', 'tanggalMulai', 'tanggalSelesai',
            'kamarId', 'kamarLabel', 'daftarKamar', 'santris', 'allDates', 'waktuList', 'semuaWaktu',
            'filterStatus', 'rekapData',
            'totalHadir', 'totalMasbuq', 'totalIzin', 'totalAlfa', 'totalDiisiAll', 'persentaseUmum',
            'statusLabels'
        ) + ['filterWaktu' => $filterWaktuParam]);
    }

    public function store(Request $request)
    {
        $tanggal = $request->tanggal;
        $waktu = $request->waktu_sholat;

        if ($request->has('statuses')) {
            foreach ($request->statuses as $santriId => $status) {
                PresensiSholat::updateOrCreate(
                    [
                        'santri_id'    => $santriId,
                        'tanggal'      => $tanggal,
                        'waktu_sholat' => $waktu,
                    ],
                    [
                        'status'  => $status,
                        'catatan' => $request->catatans[$santriId] ?? null,
                    ]
                );
            }
        }

        return back()->with('success', 'Data presensi sholat berhasil disimpan!');
    }

    public function rankingAlfa(Request $request)
    {
        $kamarId = $request->get('kamar_id', 'all');
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $daftarKamar = Kamar::urutRayon(Kamar::all());

        $santriQuery = Santri::query()
            ->where('is_pengajar', true);

        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }

        if (SchemaCache::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery->with(['kamar:id,nama_kamar'])->get();

        // Bobot poin pelanggaran
        $bobot = ['Alfa' => 5, 'Masbuq' => 2, 'Izin' => 1];

        // Angka pelanggaran dari SATU query agregat. Sebelumnya SELURUH baris
        // presensi (Alfa/Masbuq/Izin) di-hydrate jadi model Eloquent per santri
        // lalu dihitung di PHP — N+1 dan memori besar untuk rentang panjang.
        $agg = [];
        if ($santris->isNotEmpty()) {
            DB::table('presensi_sholats')
                ->select('santri_id', 'status', DB::raw('COUNT(*) as c'))
                ->whereIn('santri_id', $santris->pluck('id')->all())
                ->whereIn('status', ['Alfa', 'Masbuq', 'Izin'])
                ->whereBetween('tanggal', [$start_date, $end_date])
                ->groupBy('santri_id', 'status')
                ->get()
                ->each(function ($row) use (&$agg) {
                    $agg[(int) $row->santri_id][(string) $row->status] = (int) $row->c;
                });
        }

        $rankingData = $santris->map(function ($santri) use ($bobot, $agg) {
            $rows = $agg[$santri->id] ?? [];
            $alfaCount = $rows['Alfa'] ?? 0;
            $masbuqCount = $rows['Masbuq'] ?? 0;
            $izinCount = $rows['Izin'] ?? 0;
            $poin = $alfaCount * $bobot['Alfa']
                + $masbuqCount * $bobot['Masbuq']
                + $izinCount * $bobot['Izin'];

            return [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'jabatan' => $santri->jabatan,
                'nis' => $santri->nis,
                'kamar' => $santri->kamar?->nama_kamar ?? '-',
                'alfa_count' => $alfaCount,
                'masbuq_count' => $masbuqCount,
                'izin_count' => $izinCount,
                'poin' => $poin,
            ];
        })->sortByDesc('alfa_count')->values();

        return view('presensi.ranking-alfa', compact(
            'kamarId', 'daftarKamar', 'start_date', 'end_date', 'rankingData'
        ));
    }

    public function rankingBerjamaah(Request $request)
    {
        $kamarId = $request->get('kamar_id', 'all');
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $daftarKamar = Kamar::urutRayon(Kamar::all());

        $allWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];

        $santriQuery = Santri::query()
            ->where('is_pengajar', true);

        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }

        if (SchemaCache::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery->with(['kamar:id,nama_kamar'])->get();

        // Angka kehadiran dari SATU query agregat. Sebelumnya SELURUH
        // baris presensi di-hydrate jadi model per santri, lalu di-loop
        // hari-per-hari x 5 waktu di PHP. Dengan unique key
        // (santri_id, tanggal, waktu_sholat), COUNT per status = hasil loop.
        $agg = [];
        if ($santris->isNotEmpty()) {
            DB::table('presensi_sholats')
                ->select('santri_id', 'status', DB::raw('COUNT(*) as c'))
                ->whereIn('santri_id', $santris->pluck('id')->all())
                ->whereIn('waktu_sholat', $allWaktu)
                ->whereBetween('tanggal', [$start_date, $end_date])
                ->groupBy('santri_id', 'status')
                ->get()
                ->each(function ($row) use (&$agg) {
                    $agg[(int) $row->santri_id][(string) $row->status] = (int) $row->c;
                });
        }

        $rankingData = $santris->map(function ($santri) use ($allWaktu, $start_date, $end_date, $agg) {
            $rows = $agg[$santri->id] ?? [];
            $hadirCount = $rows['Jamaah'] ?? 0;
            $masbuqCount = $rows['Masbuq'] ?? 0;
            $izinCount = $rows['Izin'] ?? 0;
            $alfaCount = $rows['Alfa'] ?? 0;

            $start = Carbon::parse($start_date);
            $end = Carbon::parse($end_date);
            $totalJadwal = max(0, $start->diffInDays($end) + 1) * count($allWaktu);

            $totalIsi = $hadirCount + $masbuqCount + $izinCount + $alfaCount;
            $persentase = $totalJadwal > 0 ? round(($hadirCount / $totalJadwal) * 100) : 0;

            return [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'jabatan' => $santri->jabatan,
                'nis' => $santri->nis,
                'kamar' => $santri->kamar?->nama_kamar ?? '-',
                'hadir' => $hadirCount,
                'masbuq' => $masbuqCount,
                'izin' => $izinCount,
                'alfa' => $alfaCount,
                'total_jadwal' => $totalJadwal,
                'persentase' => $persentase,
            ];
        })->sortByDesc('persentase')->sortByDesc('hadir')->values();

        return view('presensi.ranking-berjamaah', compact(
            'kamarId', 'daftarKamar', 'start_date', 'end_date', 'rankingData'
        ));
    }

    public function rekapBerjamaah(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));
        $kamarId = $this->normalizeRayonFilter($request->get('kamar_ids', $request->get('kamar_id', 'all')));
        $waktuFilter = $this->normalizeWaktuFilter($request->get('waktu', 'all'));

        [$rekapData, $allDates, $showWaktu, $tanggalMulai, $tanggalSelesai] =
            $this->buildRekapBulanan($bulan, $kamarId, $waktuFilter);

        $daftarKamar = Kamar::urutRayon(Kamar::whereHas('santris')->get());

        $weeks = [
            ['label' => 'Minggu 1', 'range' => '1-' . min(7, $tanggalSelesai->day) . ' ' . $tanggalMulai->shortMonthName, 'start' => 1, 'end' => 7],
            ['label' => 'Minggu 2', 'range' => '8-14 ' . $tanggalMulai->shortMonthName, 'start' => 8, 'end' => 14],
            ['label' => 'Minggu 3', 'range' => '15-21 ' . $tanggalMulai->shortMonthName, 'start' => 15, 'end' => 21],
            ['label' => 'Minggu 4', 'range' => '22-' . $tanggalSelesai->day . ' ' . $tanggalMulai->shortMonthName, 'start' => 22, 'end' => $tanggalSelesai->day],
        ];

        return view('presensi.rekap-berjamaah', compact(
            'bulan', 'kamarId', 'waktuFilter', 'daftarKamar', 'allDates', 'showWaktu', 'rekapData',
            'tanggalMulai', 'tanggalSelesai', 'weeks'
        ));
    }

    public function exportRekapBerjamaah(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));
        $kamarId = $this->normalizeRayonFilter($request->get('kamar_ids', $request->get('kamar_id', 'all')));
        $waktuFilter = $this->normalizeWaktuFilter($request->get('waktu', 'all'));

        [$rekapData, $allDates, $showWaktu, $tanggalMulai] =
            $this->buildRekapBulanan($bulan, $kamarId, $waktuFilter);

        $abbr = ['Jamaah' => 'H', 'Masbuq' => 'M', 'Izin' => 'I', 'Alfa' => 'A'];

        $fileName = 'Absen_' . str_replace('-', '_', $bulan)
            . ($kamarId !== 'all' ? '_rayon' . str_replace(',', '_', $kamarId) : '')
            . ($waktuFilter !== 'all' ? '_' . $waktuFilter : '') . '.csv';

        $callback = function () use ($rekapData, $allDates, $showWaktu, $abbr, $tanggalMulai) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $daysInMonth = (int) $tanggalMulai->format('t');

            // Tabel Absensi: 2 baris header persis PREVIEW.HTML (NO, NAMA, TANGGAL sebagai span / nomor 1..N)
            fputcsv($out, array_merge(
                ['NO', 'NAMA'],
                array_fill(0, $daysInMonth, 'TANGGAL')
            ), ';');

            fputcsv($out, array_merge(
                ['', ''],
                array_map(fn ($d) => $d, range(1, $daysInMonth))
            ), ';');

            // Satu huruf per hari seperti PREVIEW.HTML; bila ada beberapa waktu sholat, ambil status terburuk
            $priority = ['Alfa' => 4, 'Izin' => 3, 'Masbuq' => 2, 'Jamaah' => 1];
            $rollup = [];

            foreach ($rekapData as $i => $row) {
                $perDay = [];
                $H = 0; $M = 0; $I = 0; $A = 0;

                foreach ($row['hariDetail'] as $hari) {
                    $day = (int) date('j', strtotime($hari['date']));

                    $best = null;
                    $bestScore = 0;
                    foreach ($showWaktu as $w) {
                        $st = $hari['statuses'][$w] ?? '-';
                        $score = $priority[$st] ?? 0;
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $best = $st;
                        }
                    }

                    $letter = $best ? ($abbr[$best] ?? '') : '';
                    $perDay[$day] = $letter;

                    match ($letter) {
                        'H' => $H++,
                        'M' => $M++,
                        'I' => $I++,
                        'A' => $A++,
                        default => null,
                    };
                }

                $cells = [];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $cells[] = $perDay[$d] ?? '';
                }

                $rollup[] = ['no' => $i + 1, 'nama' => $row['nama'], 'H' => $H, 'M' => $M, 'I' => $I, 'A' => $A];

                fputcsv($out, array_merge(
                    [$i + 1, $row['nama']],
                    $cells
                ), ';');
            }

            // Bagian Rekapitulasi: judul + dua tabel (recap1 & recap2) seperti PREVIEW.HTML
            fputcsv($out, []);
            fputcsv($out, ['RINCIAN REKAPITULASI KEHADIRAN']);
            fputcsv($out, []);

            $mid = (int) ceil(count($rollup) / 2);

            foreach ([array_slice($rollup, 0, $mid), array_slice($rollup, $mid)] as $block) {
                fputcsv($out, ['NO', 'NAMA', 'HDR', 'MSB', 'IZN', 'ALF', '%'], ';');
                foreach ($block as $r) {
                    $filled = $r['H'] + $r['M'] + $r['I'] + $r['A'];
                    $pct = $filled > 0 ? round(($r['H'] + $r['M']) / $filled * 100) : 0;
                    fputcsv($out, [
                        $r['no'], $r['nama'],
                        $r['H'], $r['M'], $r['I'], $r['A'], $pct . '%',
                    ], ';');
                }
                fputcsv($out, []);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Normalisasi filter waktu sholat dari request (array checkbox / CSV / 'all').
     * Mengembalikan 'all' untuk semua, atau string CSV terurut sesuai urutan waktu baku.
     */
    protected function normalizeWaktuFilter($waktuFilter): string
    {
        $allWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];

        if (is_array($waktuFilter)) {
            if (in_array('all', $waktuFilter)) return 'all';
            $waktuFilter = implode(',', $waktuFilter);
        }

        if ($waktuFilter === null || $waktuFilter === '' || $waktuFilter === 'all') {
            return 'all';
        }

        $list = array_values(array_intersect($allWaktu, array_map('trim', explode(',', (string)$waktuFilter))));

        return count($list) === count($allWaktu) ? 'all' : implode(',', $list);
    }

    /**
     * Normalisasi filter rayon/kamar dari request (array checkbox / CSV / single id / 'all').
     * Mengembalikan 'all' untuk semua rayon, atau string CSV id rayon.
     */
    protected function normalizeRayonFilter($kamarFilter): string
    {
        if (is_array($kamarFilter)) {
            if (in_array('all', $kamarFilter)) return 'all';
            $kamarFilter = implode(',', $kamarFilter);
        }

        if ($kamarFilter === null || $kamarFilter === '' || $kamarFilter === 'all') {
            return 'all';
        }

        $ids = array_values(array_filter(array_map('trim', explode(',', (string)$kamarFilter))));

        return count($ids) === 0 ? 'all' : implode(',', $ids);
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: array, 2: array, 3: \Carbon\Carbon, 4: \Carbon\Carbon}
     */
    protected function buildRekapBulanan(string $bulan, $kamarId, $waktuFilter): array
    {
        $ref = Carbon::parse($bulan . '-01');
        $tanggalMulai = $ref->copy()->startOfMonth();
        // Untuk bulan berjalan: tutup di tanggal hari ini (konsisten dengan Rating Berjamaah).
        // Untuk bulan lampau: gunakan akhir bulan penuh.
        $tanggalSelesai = ($bulan === Carbon::now()->format('Y-m'))
            ? Carbon::now()->copy()->endOfDay()
            : $ref->copy()->endOfMonth();

        $allWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];

        // Support multi-waktu: array atau CSV string. 'all' di dalam array = semua.
        $normalized = [];
        if (is_array($waktuFilter)) {
            $normalized = $waktuFilter;
        } elseif ($waktuFilter !== 'all' && $waktuFilter !== '') {
            $normalized = array_map('trim', explode(',', $waktuFilter));
        }
        if (in_array('all', $normalized)) {
            $showWaktu = $allWaktu;
        } elseif (count($normalized) > 0) {
            $showWaktu = array_values(array_intersect($allWaktu, $normalized));
        } else {
            $showWaktu = $allWaktu;
        }

        $allDates = [];
        $temp = $tanggalMulai->copy();
        while ($temp->lte($tanggalSelesai)) {
            $allDates[] = $temp->copy();
            $temp->addDay();
        }

        $santriQuery = Santri::query()
            ->where('is_pengajar', true);
        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }
        if (SchemaCache::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery
            ->with(['kamar:id,nama_kamar'])
            ->orderBy('nama', 'asc')
            ->get();

        // Status presensi dari SATU query baris mentah (DB::table),
        // tanpa menghydrate tiap baris jadi model Eloquent. Untuk
        // 300 santri x 30 hari x 5 waktu, hydrate model sebelumnya
        // memakan memori besar dan waktu — pemicu "502 Bad Gateway".
        $presensiBySantri = [];
        if ($santris->isNotEmpty()) {
            DB::table('presensi_sholats')
                ->select('santri_id', 'tanggal', 'waktu_sholat', 'status')
                ->whereIn('santri_id', $santris->pluck('id')->all())
                ->where('tanggal', '>=', $tanggalMulai->format('Y-m-d'))
                ->where('tanggal', '<=', $tanggalSelesai->format('Y-m-d'))
                ->whereIn('waktu_sholat', $showWaktu)
                ->get()
                ->each(function ($row) use (&$presensiBySantri) {
                    $presensiBySantri[(int) $row->santri_id]
                        [$row->tanggal . '|' . $row->waktu_sholat] = $row->status;
                });
        }

        $rekapData = $santris->map(function ($santri) use ($allDates, $showWaktu, $presensiBySantri) {
            $keyed = $presensiBySantri[$santri->id] ?? [];

            $hariDetail = [];
            foreach ($allDates as $date) {
                $dateStr = $date->format('Y-m-d');
                $statuses = [];
                foreach ($showWaktu as $waktu) {
                    $statuses[$waktu] = $keyed[$dateStr . '|' . $waktu] ?? '-';
                }
                $hariDetail[] = [
                    'date' => $dateStr,
                    'label' => $date->isoFormat('ddd, D MMM'),
                    'statuses' => $statuses,
                ];
            }

            $hadir = 0; $masbuq = 0; $izin = 0; $alfa = 0;
            foreach ($hariDetail as $hari) {
                foreach ($hari['statuses'] as $st) {
                    match ($st) {
                        'Jamaah' => $hadir++,
                        'Masbuq' => $masbuq++,
                        'Izin'   => $izin++,
                        'Alfa'   => $alfa++,
                        default  => null,
                    };
                }
            }

            $totalJadwal = count($allDates) * count($showWaktu);
            $persentase = $totalJadwal > 0 ? round(($hadir / $totalJadwal) * 100) : 0;

            return [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'jabatan' => $santri->jabatan,
                'nis' => $santri->nis,
                'kamar' => $santri->kamar?->nama_kamar ?? '-',
                'hariDetail' => $hariDetail,
                'hadir' => $hadir,
                'masbuq' => $masbuq,
                'izin' => $izin,
                'alfa' => $alfa,
                'total_jadwal' => $totalJadwal,
                'persentase' => $persentase,
            ];
        })->sortByDesc('persentase')->sortByDesc('hadir')->values();

        return [$rekapData, $allDates, $showWaktu, $tanggalMulai, $tanggalSelesai];
    }

    public function quickStatus(Request $request)
    {
        $santriId = $request->santri_id;
        $tanggal = $request->tanggal;
        $waktuSholat = $request->waktu_sholat;
        $status = $request->status;

        $existing = PresensiSholat::where('santri_id', $santriId)
            ->where('tanggal', $tanggal)
            ->where('waktu_sholat', $waktuSholat)
            ->first();
        $statusLama = $existing?->status;

        if ($status === '-' || $status === null || $status === '') {
            PresensiSholat::where('santri_id', $santriId)
                ->where('tanggal', $tanggal)
                ->where('waktu_sholat', $waktuSholat)
                ->delete();

            \App\Models\PresensiLog::create([
                'user_id' => auth()->id(),
                'santri_id' => $santriId,
                'tanggal' => $tanggal,
                'waktu_sholat' => $waktuSholat,
                'status_lama' => $statusLama,
                'status_baru' => null,
                'aksi' => 'Kosongkan',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Presensi dikosongkan.',
            ]);
        }

        PresensiSholat::updateOrCreate(
            [
                'santri_id'    => $santriId,
                'tanggal'      => $tanggal,
                'waktu_sholat' => $waktuSholat,
            ],
            [
                'status' => $status,
                'catatan' => $request->catatan ?? null,
            ]
        );

        if ($statusLama !== $status) {
            \App\Models\PresensiLog::create([
                'user_id' => auth()->id(),
                'santri_id' => $santriId,
                'tanggal' => $tanggal,
                'waktu_sholat' => $waktuSholat,
                'status_lama' => $statusLama,
                'status_baru' => $status,
                'aksi' => $statusLama ? 'Ubah' : 'Isi',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Status santri berhasil diperbarui menjadi: {$status}",
        ]);
    }

    public function hapusPresensi(Request $request)
    {
        $tanggal = $request->get('tanggal');
        $waktuSholat = $request->get('waktu_sholat');
        $kamarId = $request->get('kamar_id');

        if (!$tanggal || !$waktuSholat) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap.'], 400);
        }

        $query = PresensiSholat::where('tanggal', $tanggal)
            ->where('waktu_sholat', $waktuSholat);

        if ($kamarId && $kamarId !== 'all') {
            $query->whereHas('santri', function ($q) use ($kamarId) {
                $q->where('kamar_id', $kamarId);
            });
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} data presensi berhasil dihapus.",
            'deleted' => $deleted,
        ]);
    }
}
