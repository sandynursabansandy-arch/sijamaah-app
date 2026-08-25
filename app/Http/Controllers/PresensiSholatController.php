<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\PresensiSholat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PresensiSholatController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $waktu = $request->get('waktu_sholat', 'Subuh');
        $kamarId = $request->get('kamar_id', 'all');
        $periodRating = $request->get('period', 7);
        $chartFilter = $request->get('chart_filter', 'minggu');
        $chartStart = $request->get('chart_start');
        $chartEnd = $request->get('chart_end');

        $perOrangPeriode = $request->get('per_orang_periode', 'minggu');
        $perOrangStart = $request->get('per_orang_start');
        $perOrangEnd = $request->get('per_orang_end');
        $perOrangWaktu = $request->get('per_orang_waktu', 'all');

        $daftarKamar = Kamar::all();

        $santriQuery = Santri::query();

        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }

        if (Schema::hasColumn('santris', 'status')) {
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

        $chartData = $this->buildChartData($chartFilter, $chartStart, $chartEnd);
        $statusBreakdown = $this->buildStatusBreakdown($tanggal, $waktu);

        $perOrangFilterNama = null;
        if (auth()->check() && !auth()->user()->isAdmin()) {
            $perOrangFilterNama = auth()->user()->name;
        }
        $perOrangData = $this->buildPerOrangData($kamarId, $perOrangPeriode, $perOrangStart, $perOrangEnd, $perOrangWaktu, $perOrangFilterNama);

        $topRating = $santris->sortByDesc('rating')->values();

        return view('presensi.index', compact(
            'tanggal', 'waktu', 'kamarId', 'daftarKamar', 'santris',
            'periodRating', 'chartData', 'statusBreakdown', 'chartFilter',
            'chartStart', 'chartEnd', 'topRating', 'perOrangData',
            'perOrangPeriode', 'perOrangStart', 'perOrangEnd', 'perOrangWaktu'
        ) + [
            'alfaBeruntun' => $this->getAlfaBeruntun(),
            'trenBulanan' => $this->getTrenBulanan(),
        ]);
    }

    /**
     * Santri dengan alfa >= 3 hari beruntun (berdasarkan tanggal terakhir alfa).
     */
    protected function getAlfaBeruntun(int $minStreak = 3): array
    {
        return \App\Services\PresensiAnalysis::alfaBeruntun($minStreak);
    }

    /**
     * Persentase kehadiran (Jamaah / total terisi) per bulan, 6 bulan terakhir.
     */
    protected function getTrenBulanan(int $jumlahBulan = 6): array
    {
        $start = Carbon::now()->subMonths($jumlahBulan - 1)->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $presensis = PresensiSholat::whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->with('santri')
            ->get(['santri_id', 'tanggal', 'status']);

        $grouped = [];
        foreach ($presensis as $p) {
            $key = $p->tanggal->format('Y-m');
            $grouped[$key]['total'] = ($grouped[$key]['total'] ?? 0) + 1;
            $grouped[$key]['hadir'] = ($grouped[$key]['hadir'] ?? 0) + ($p->status === 'Jamaah' ? 1 : 0);
            $statusKey = match ($p->status) {
                'Jamaah' => 'Hadir',
                'Masbuq', 'Izin', 'Alfa' => $p->status,
                default => null,
            };
            if ($statusKey) {
                $grouped[$key]['breakdown'][$statusKey][] = [
                    'nama' => $p->santri?->nama ?? 'Unknown',
                    'tanggal' => $p->tanggal->format('Y-m-d'),
                ];
            }
        }

        $tren = [];
        for ($i = $jumlahBulan - 1; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $key = $m->format('Y-m');
            $g = $grouped[$key] ?? [];
            $total = (int) ($g['total'] ?? 0);
            $hadir = (int) ($g['hadir'] ?? 0);

            $tren[] = [
                'label' => $m->translatedFormat('M'),
                'pct' => $total > 0 ? round(($hadir / $total) * 100) : 0,
                'detail' => [
                    'label' => $m->translatedFormat('F Y'),
                    'total' => $total,
                    'hadir' => $hadir,
                    'breakdown' => [
                        'Hadir' => $g['breakdown']['Hadir'] ?? [],
                        'Masbuq' => $g['breakdown']['Masbuq'] ?? [],
                        'Izin' => $g['breakdown']['Izin'] ?? [],
                        'Alfa' => $g['breakdown']['Alfa'] ?? [],
                    ],
                ],
            ];
        }

        return $tren;
    }

    protected function buildChartData(string $filter = 'minggu', ?string $startDate = null, ?string $endDate = null): array
    {
        $chartData = match ($filter) {
            'minggu' => $this->buildMingguanData($startDate, $endDate),
            'bulan' => $this->buildBulananData($startDate, $endDate),
            'tahun' => $this->buildTahunanData(),
            default => $this->buildMingguanData($startDate, $endDate),
        };

        $rangeStart = $chartData['dateRange']['start'];
        $rangeEnd = $chartData['dateRange']['end'];

        $waktuSholatLabels = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $presensiPerWaktu = [];
        $waktuBreakdownDetail = [];

        foreach ($waktuSholatLabels as $waktuLabel) {
            $result = PresensiSholat::query()
                ->where('waktu_sholat', $waktuLabel)
                ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "Jamaah" THEN 1 ELSE 0 END) as jamaah, SUM(CASE WHEN status = "Masbuq" THEN 1 ELSE 0 END) as masbuq, SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as izin, SUM(CASE WHEN status = "Alfa" THEN 1 ELSE 0 END) as alfa')
                ->first();

            $total = (int) ($result->total ?? 0);
            $jamaah = (int) ($result->jamaah ?? 0);
            $presensiPerWaktu[] = $total > 0 ? round(($jamaah / $total) * 100) : 0;

            $waktuBreakdownDetail[$waktuLabel] = [
                'total' => $total,
                'jamaah' => $jamaah,
                'masbuq' => (int) ($result->masbuq ?? 0),
                'izin' => (int) ($result->izin ?? 0),
                'alfa' => (int) ($result->alfa ?? 0),
                'percentage' => $total > 0 ? round(($jamaah / $total) * 100) : 0,
                'names' => [
                    'Jamaah' => PresensiSholat::where('waktu_sholat', $waktuLabel)->whereBetween('tanggal', [$rangeStart, $rangeEnd])->where('status', 'Jamaah')->with('santri')->get()->map(fn($p) => ['nama' => $p->santri?->nama ?? 'Unknown', 'tanggal' => $p->tanggal])->toArray(),
                    'Masbuq' => PresensiSholat::where('waktu_sholat', $waktuLabel)->whereBetween('tanggal', [$rangeStart, $rangeEnd])->where('status', 'Masbuq')->with('santri')->get()->map(fn($p) => ['nama' => $p->santri?->nama ?? 'Unknown', 'tanggal' => $p->tanggal])->toArray(),
                    'Izin' => PresensiSholat::where('waktu_sholat', $waktuLabel)->whereBetween('tanggal', [$rangeStart, $rangeEnd])->where('status', 'Izin')->with('santri')->get()->map(fn($p) => ['nama' => $p->santri?->nama ?? 'Unknown', 'tanggal' => $p->tanggal])->toArray(),
                    'Alfa' => PresensiSholat::where('waktu_sholat', $waktuLabel)->whereBetween('tanggal', [$rangeStart, $rangeEnd])->where('status', 'Alfa')->with('santri')->get()->map(fn($p) => ['nama' => $p->santri?->nama ?? 'Unknown', 'tanggal' => $p->tanggal])->toArray(),
                ],
            ];
        }

        $statusKehadiran = [
            'Hadir' => PresensiSholat::query()->where('status', 'Jamaah')->whereBetween('tanggal', [$rangeStart, $rangeEnd])->count(),
            'Masbuq' => PresensiSholat::query()->where('status', 'Masbuq')->whereBetween('tanggal', [$rangeStart, $rangeEnd])->count(),
            'Izin' => PresensiSholat::query()->where('status', 'Izin')->whereBetween('tanggal', [$rangeStart, $rangeEnd])->count(),
            'Alfa' => PresensiSholat::query()->where('status', 'Alfa')->whereBetween('tanggal', [$rangeStart, $rangeEnd])->count(),
        ];

        $chartData['presensiPerWaktu'] = [
            'labels' => $waktuSholatLabels,
            'values' => $presensiPerWaktu,
            'subtitle' => Carbon::parse($rangeStart)->translatedFormat('d M') . ' - ' . Carbon::parse($rangeEnd)->translatedFormat('d M Y'),
        ];
        $chartData['waktuBreakdownDetail'] = $waktuBreakdownDetail;
        $chartData['statusKehadiran'] = $statusKehadiran;

        return $chartData;
    }

    protected function buildMingguanData(?string $startDate = null, ?string $endDate = null): array
    {
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now();
        $start = $startDate ? Carbon::parse($startDate) : $end->copy()->subDays(6);

        $labels = [];
        $detailLabels = [];
        $values = [];
        $detailData = [];

        $days = (int) $start->diffInDays($end) + 1;
        $days = min($days, 90);

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->translatedFormat('d M');
            $detailLabels[] = $date->translatedFormat('d M Y');

            $dayData = PresensiSholat::query()
                ->where('tanggal', $dateStr)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "Jamaah" THEN 1 ELSE 0 END) as hadir')
                ->first();

            $total = (int) ($dayData->total ?? 0);
            $hadir = (int) ($dayData->hadir ?? 0);
            $values[] = $total > 0 ? round(($hadir / $total) * 100) : 0;

            $statusBreakdown = [];
            foreach (['Jamaah' => 'Hadir', 'Masbuq' => 'Masbuq', 'Izin' => 'Izin', 'Alfa' => 'Alfa'] as $dbStatus => $label) {
                $names = PresensiSholat::where('tanggal', $dateStr)
                    ->where('status', $dbStatus)
                    ->with('santri')
                    ->get()
                    ->map(fn($p) => ['nama' => $p->santri?->nama ?? 'Unknown', 'tanggal' => $p->tanggal])
                    ->toArray();
                $statusBreakdown[$label] = $names;
            }

            $detailData[] = [
                'label' => $date->translatedFormat('d M Y'),
                'total' => $total,
                'hadir' => $hadir,
                'breakdown' => $statusBreakdown,
            ];
        }

        return [
            'presensiBulanan' => [
                'labels' => $labels,
                'detailLabels' => $detailLabels,
                'values' => $values,
                'detailData' => $detailData,
                'title' => 'Kehadiran Per Hari',
                'subtitle' => $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y'),
            ],
            'dateRange' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
        ];
    }

    protected function buildBulananData(?string $startMonth = null, ?string $endMonth = null): array
    {
        $end = $endMonth ? Carbon::parse($endMonth)->endOfMonth() : Carbon::now();
        $start = $startMonth ? Carbon::parse($startMonth)->startOfMonth() : $end->copy()->subMonths(5)->startOfMonth();

        $labels = [];
        $detailLabels = [];
        $values = [];
        $detailData = [];

        $months = (int) $start->diffInMonths($end) + 1;
        $months = min($months, 24);

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $detailLabels[] = $month->translatedFormat('M Y');

            $periodStart = $month->copy()->startOfMonth()->format('Y-m-d');
            $periodEnd = $month->copy()->endOfMonth()->format('Y-m-d');

            $monthData = PresensiSholat::query()
                ->whereBetween('tanggal', [$periodStart, $periodEnd])
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "Jamaah" THEN 1 ELSE 0 END) as hadir')
                ->first();

            $total = (int) ($monthData->total ?? 0);
            $hadir = (int) ($monthData->hadir ?? 0);
            $values[] = $total > 0 ? round(($hadir / $total) * 100) : 0;

            $statusBreakdown = [];
            foreach (['Jamaah' => 'Hadir', 'Masbuq' => 'Masbuq', 'Izin' => 'Izin', 'Alfa' => 'Alfa'] as $dbStatus => $label) {
                $names = PresensiSholat::whereBetween('tanggal', [$periodStart, $periodEnd])
                    ->where('status', $dbStatus)
                    ->with('santri')
                    ->get()
                    ->map(fn($p) => $p->santri?->nama ?? 'Unknown')
                    ->toArray();
                $statusBreakdown[$label] = $names;
            }

            $detailData[] = [
                'label' => $month->translatedFormat('F Y'),
                'total' => $total,
                'hadir' => $hadir,
                'breakdown' => $statusBreakdown,
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

    protected function buildTahunanData(): array
    {
        $labels = [];
        $detailLabels = [];
        $values = [];
        $detailData = [];

        for ($i = 2; $i >= 0; $i--) {
            $year = now()->subYears($i);
            $yearLabel = $year->translatedFormat('Y');
            $labels[] = $yearLabel;
            $detailLabels[] = $yearLabel;

            $periodStart = $year->copy()->startOfYear()->format('Y-m-d');
            $periodEnd = $year->copy()->endOfYear()->format('Y-m-d');

            $yearData = PresensiSholat::query()
                ->whereBetween('tanggal', [$periodStart, $periodEnd])
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "Jamaah" THEN 1 ELSE 0 END) as hadir')
                ->first();

            $total = (int) ($yearData->total ?? 0);
            $hadir = (int) ($yearData->hadir ?? 0);
            $values[] = $total > 0 ? round(($hadir / $total) * 100) : 0;

            $statusBreakdown = [];
            foreach (['Jamaah' => 'Hadir', 'Masbuq' => 'Masbuq', 'Izin' => 'Izin', 'Alfa' => 'Alfa'] as $dbStatus => $label) {
                $names = PresensiSholat::whereBetween('tanggal', [$periodStart, $periodEnd])
                    ->where('status', $dbStatus)
                    ->with('santri')
                    ->get()
                    ->map(fn($p) => $p->santri?->nama ?? 'Unknown')
                    ->toArray();
                $statusBreakdown[$label] = $names;
            }

            $detailData[] = [
                'label' => $yearLabel,
                'total' => $total,
                'hadir' => $hadir,
                'breakdown' => $statusBreakdown,
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

    protected function buildStatusBreakdown(string $tanggal, string $waktu): array
    {
        $statuses = ['Jamaah', 'Masbuq', 'Izin', 'Alfa'];
        $breakdown = [];

        foreach ($statuses as $status) {
            $santris = PresensiSholat::where('tanggal', $tanggal)
                ->where('waktu_sholat', $waktu)
                ->where('status', $status)
                ->with('santri')
                ->get()
                ->map(function ($presensi) {
                    return [
                        'nama' => $presensi->santri?->nama ?? 'Unknown',
                        'nis' => $presensi->santri?->nis ?? '-',
                        'kamar' => $presensi->santri?->kamar?->nama_kamar ?? '-',
                        'jabatan' => $presensi->santri?->jabatan ?? '-',
                    ];
                });

            $breakdown[$status === 'Jamaah' ? 'Hadir' : $status] = $santris;
        }

        return $breakdown;
    }

    protected function buildPerOrangData(?string $kamarId, string $periode = 'minggu', ?string $startDate = null, ?string $endDate = null, ?string $waktuSholat = null, ?string $filterNama = null): array
    {
        $ref = now();
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

        $santriQuery = Santri::query();
        if ($kamarId && $kamarId !== 'all' && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }
        if (Schema::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }
        if ($filterNama) {
            $santriQuery->where('nama', $filterNama);
        }

        $santris = $santriQuery
            ->with(['presensis' => function ($q) use ($startStr, $endStr, $waktuList) {
                $q->where('tanggal', '>=', $startStr)
                  ->where('tanggal', '<=', $endStr)
                  ->whereIn('waktu_sholat', $waktuList);
            }])
            ->orderBy('nama', 'asc')
            ->get();

        $days = (int) $start->diffInDays($end) + 1;
        $totalJadwal = $days * count($waktuList);

        $perOrang = $santris->map(function ($santri) use ($totalJadwal) {
            $hadir = 0; $masbuq = 0; $izin = 0; $alfa = 0;
            foreach ($santri->presensis as $p) {
                match ($p->status) {
                    'Jamaah' => $hadir++,
                    'Masbuq' => $masbuq++,
                    'Izin'   => $izin++,
                    'Alfa'   => $alfa++,
                    default  => null,
                };
            }
            $totalIsi = $hadir + $masbuq + $izin + $alfa;
            $persentase = $totalJadwal > 0 ? round(($hadir / $totalJadwal) * 100) : 0;

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
        })->sortByDesc('persentase')->sortByDesc('hadir')->values();

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

        $daftarKamar = Kamar::all();

        $santriQuery = Santri::query();

        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }

        if (Schema::hasColumn('santris', 'status')) {
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
        $kamarId      = $request->get('kamar_id', 'all');
        $filterWaktu  = $request->get('filter_waktu', 'all');
        $filterStatus = $request->get('filter_status', '');

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

        $daftarKamar = Kamar::all();

        $semuaWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $waktuList  = $filterWaktu !== 'all' ? [$filterWaktu] : $semuaWaktu;

        $allDates = [];
        $temp = $tanggalMulai->copy();
        while ($temp->lte($tanggalSelesai)) {
            $allDates[] = $temp->copy();
            $temp->addDay();
        }

        $santriQuery = Santri::query();
        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }
        if (Schema::hasColumn('santris', 'status')) {
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

        $statusLabels = [
            'alfa'   => 'Alfa',
            'masbuq' => 'Masbuq',
            'izin'   => 'Izin',
        ];

        return view('presensi.rekap-cetak', compact(
            'periode', 'periodeLabel', 'tanggalMulai', 'tanggalSelesai',
            'kamarId', 'daftarKamar', 'santris', 'allDates', 'waktuList', 'semuaWaktu',
            'filterWaktu', 'filterStatus', 'rekapData',
            'totalHadir', 'totalMasbuq', 'totalIzin', 'totalAlfa', 'totalDiisiAll', 'persentaseUmum',
            'statusLabels'
        ));
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
        $daftarKamar = Kamar::all();

        $santriQuery = Santri::query();

        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }

        if (Schema::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery->with(['presensis' => function ($query) use ($start_date, $end_date) {
            $query->whereIn('status', ['Alfa', 'Masbuq', 'Izin'])
                  ->whereBetween('tanggal', [$start_date, $end_date]);
        }])->get();

        // Bobot poin pelanggaran
        $bobot = ['Alfa' => 5, 'Masbuq' => 2, 'Izin' => 1];

        $rankingData = $santris->map(function ($santri) use ($bobot) {
            $alfaCount = 0; $masbuqCount = 0; $izinCount = 0; $poin = 0;
            foreach ($santri->presensis as $p) {
                match ($p->status) {
                    'Alfa'   => $alfaCount++,
                    'Masbuq' => $masbuqCount++,
                    'Izin'   => $izinCount++,
                    default  => null,
                };
                $poin += $bobot[$p->status] ?? 0;
            }

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
        $daftarKamar = Kamar::all();

        $allWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];

        $santriQuery = Santri::query();

        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }

        if (Schema::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery->with(['presensis' => function ($query) use ($start_date, $end_date) {
            $query->whereBetween('tanggal', [$start_date, $end_date]);
        }])->get();

        $rankingData = $santris->map(function ($santri) use ($allWaktu, $start_date, $end_date) {
            $presensiByDate = $santri->presensis->keyBy(fn($p) => $p->tanggal->format('Y-m-d') . '|' . $p->waktu_sholat);

            $totalJadwal = 0;
            $hadirCount = 0;
            $masbuqCount = 0;
            $izinCount = 0;
            $alfaCount = 0;

            $start = Carbon::parse($start_date);
            $end = Carbon::parse($end_date);
            $temp = $start->copy();

            while ($temp->lte($end)) {
                foreach ($allWaktu as $waktu) {
                    $totalJadwal++;
                    $key = $temp->format('Y-m-d') . '|' . $waktu;
                    $presensi = $presensiByDate->get($key);
                    if ($presensi) {
                        match ($presensi->status) {
                            'Jamaah' => $hadirCount++,
                            'Masbuq' => $masbuqCount++,
                            'Izin'   => $izinCount++,
                            'Alfa'   => $alfaCount++,
                            default  => null,
                        };
                    }
                }
                $temp->addDay();
            }

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
        $kamarId = $request->get('kamar_id', 'all');
        $waktuFilter = $request->get('waktu', 'all');

        [$rekapData, $allDates, $showWaktu, $tanggalMulai, $tanggalSelesai] =
            $this->buildRekapBulanan($bulan, $kamarId, $waktuFilter);

        $daftarKamar = Kamar::all();

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
        $kamarId = $request->get('kamar_id', 'all');
        $waktuFilter = $request->get('waktu', 'all');

        [$rekapData, $allDates, $showWaktu] =
            $this->buildRekapBulanan($bulan, $kamarId, $waktuFilter);

        $abbr = ['Jamaah' => "\xE2\x9C\x93", 'Masbuq' => 'M', 'Izin' => 'I', 'Alfa' => 'A'];

        $fileName = 'Rekap_Berjamaah_' . str_replace('-', '_', $bulan)
            . ($kamarId !== 'all' ? '_rayon' . $kamarId : '')
            . ($waktuFilter !== 'all' ? '_' . $waktuFilter : '') . '.csv';

        $callback = function () use ($rekapData, $allDates, $showWaktu, $abbr) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, array_merge(
                ['No', 'Nama Santri', 'Rayon'],
                array_map(fn ($d) => $d->isoFormat('D/M'), $allDates),
                ['Hadir', 'Masbuq', 'Izin', 'Alfa', 'Persentase']
            ), ';');

            foreach ($rekapData as $i => $row) {
                $cells = [];
                foreach ($row['hariDetail'] as $hari) {
                    $sts = [];
                    foreach ($showWaktu as $w) {
                        $st = $hari['statuses'][$w] ?? '-';
                        $sts[] = $abbr[$st] ?? '-';
                    }
                    $cells[] = implode('/', $sts);
                }

                fputcsv($out, array_merge(
                    [$i + 1, $row['nama'] . ($row['jabatan'] ? ' (' . $row['jabatan'] . ')' : ''), $row['kamar']],
                    $cells,
                    [$row['hadir'], $row['masbuq'], $row['izin'], $row['alfa'], $row['persentase'] . '%']
                ), ';');
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: array, 2: array, 3: \Carbon\Carbon, 4: \Carbon\Carbon}
     */
    protected function buildRekapBulanan(string $bulan, $kamarId, string $waktuFilter): array
    {
        $ref = Carbon::parse($bulan . '-01');
        $tanggalMulai = $ref->copy()->startOfMonth();
        $tanggalSelesai = $ref->copy()->endOfMonth();

        $allWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $showWaktu = $waktuFilter !== 'all' && in_array($waktuFilter, $allWaktu) ? [$waktuFilter] : $allWaktu;

        $allDates = [];
        $temp = $tanggalMulai->copy();
        while ($temp->lte($tanggalSelesai)) {
            $allDates[] = $temp->copy();
            $temp->addDay();
        }

        $santriQuery = Santri::query();
        if ($kamarId !== 'all' && $kamarId !== null && $kamarId !== '') {
            $santriQuery->where('kamar_id', $kamarId);
        }
        if (Schema::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }

        $santris = $santriQuery
            ->with(['presensis' => function ($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->where('tanggal', '>=', $tanggalMulai->format('Y-m-d'))
                  ->where('tanggal', '<=', $tanggalSelesai->format('Y-m-d'));
            }])
            ->orderBy('nama', 'asc')
            ->get();

        $rekapData = $santris->map(function ($santri) use ($allDates, $showWaktu) {
            $presensiKeyed = $santri->presensis->keyBy(
                fn($p) => $p->tanggal->format('Y-m-d') . '|' . $p->waktu_sholat
            );

            $hariDetail = [];
            foreach ($allDates as $date) {
                $dateStr = $date->format('Y-m-d');
                $statuses = [];
                foreach ($showWaktu as $waktu) {
                    $presensi = $presensiKeyed->get($dateStr . '|' . $waktu);
                    $statuses[$waktu] = $presensi ? $presensi->status : '-';
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
