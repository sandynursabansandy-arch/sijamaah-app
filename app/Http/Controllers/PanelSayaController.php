<?php

namespace App\Http\Controllers;

use App\Models\PresensiSholat;
use App\Models\Santri;
use App\Models\AdminMessage;
use App\Support\AdminPesan;
use Illuminate\Http\Request;

class PanelSayaController extends Controller
{
    protected const WAKTU = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];

    public function index(Request $request)
    {
        $user = auth()->user();
        $santri = Santri::where('nama', $user->name)->where('status', 'Aktif')->first();

        $bulan = (string) $request->get('bulan', date('Y-m')) ?: date('Y-m');
        $waktuFilter = (string) ($request->get('waktu') ?? 'all');

        $calendar = [];
        $monthStats = [];
        $history = collect();
        $historyTanggal = (string) ($request->get('tanggal') ?? '');

        if ($santri) {
            $calendar = $this->buildCalendar($santri, $bulan);
            $monthStats = $this->buildMonthStats($santri, $bulan);
            $history = $this->buildHistory($santri, $historyTanggal, $waktuFilter);
        }

        $notifs = app(\App\Http\Controllers\PresensiSholatController::class)->buildNotifData();
        $targets = $santri ? $this->buildTargets($santri, $bulan) : $this->emptyTargets();

        AdminPesan::ensureTable();
        $pesanMessages = $santri
            ? AdminMessage::where('santri_id', $santri->id)->orderByDesc('created_at')->limit(20)->get()
            : collect();
        $pesanUnread = $pesanMessages->where('is_read', false)->count();

        $months = collect();
        $cursor = now()->copy()->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $months->push($cursor->format('Y-m'));
            $cursor->subMonth();
        }

        return view('panelsaya.index', compact('santri', 'bulan', 'calendar', 'monthStats', 'history', 'waktuFilter', 'historyTanggal', 'months', 'notifs', 'targets', 'pesanMessages', 'pesanUnread'));
    }

    protected function buildCalendar(Santri $santri, string $bulan): array
    {
        $start = \Carbon\Carbon::parse($bulan . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $presensis = $santri->presensis()
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->groupBy(fn ($p) => $p->tanggal->format('Y-m-d'));

        $calendar = [];
        $firstDay = (int) $start->format('N'); // 1=Senin ... 7=Minggu
        for ($day = 1; $day <= (int) $start->format('t'); $day++) {
            $dateStr = $start->copy()->day($day)->format('Y-m-d');
            $statuses = $presensis->get($dateStr, collect())->keyBy('waktu_sholat');
            $calendar[] = [
                'date' => $dateStr,
                'day' => $day,
                'statuses' => $statuses,
                'jamaah' => $statuses->where('status', 'Jamaah')->count(),
                'alfa' => $statuses->where('status', 'Alfa')->count(),
            ];
        }

        return [
            'start' => $start,
            'end' => $end,
            'firstDay' => $firstDay,
            'days' => $calendar,
            'waktu' => self::WAKTU,
        ];
    }

    protected function buildMonthStats(Santri $santri, string $bulan): array
    {
        $start = \Carbon\Carbon::parse($bulan . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $presensis = $santri->presensis()
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        $totalDays = (int) $start->format('t');
        $seharusnya = $totalDays * 5;
        $jamaah = $presensis->where('status', 'Jamaah')->count();
        $masbuq = $presensis->where('status', 'Masbuq')->count();
        $izin = $presensis->where('status', 'Izin')->count();
        $alfa = $presensis->where('status', 'Alfa')->count();
        $terisi = $jamaah + $masbuq + $izin + $alfa;
        $belumDiisi = max(0, $seharusnya - $terisi);

        $rating = $seharusnya > 0 ? round(($jamaah / $seharusnya) * 100) : 0;

        $level = match (true) {
            $rating >= 90 => ['label' => 'Istimewa', 'text' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-100 text-emerald-700'],
            $rating >= 75 => ['label' => 'Rajin', 'text' => 'text-blue-600', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'badge' => 'bg-blue-100 text-blue-700'],
            $rating >= 60 => ['label' => 'Cukup', 'text' => 'text-amber-600', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'badge' => 'bg-amber-100 text-amber-700'],
            default => ['label' => 'Perlu Ditingkatkan', 'text' => 'text-red-600', 'bg' => 'bg-red-50', 'border' => 'border-red-200', 'badge' => 'bg-red-100 text-red-700'],
        };

        $weekly = [];
        $weekStartDay = 1;
        $weekNo = 1;
        while ($weekStartDay <= $totalDays) {
            $weekEndDay = min($weekStartDay + 6, $totalDays);
            $weekDays = $weekEndDay - $weekStartDay + 1;
            $wStart = $start->copy()->day($weekStartDay)->format('Y-m-d');
            $wEnd = $start->copy()->day($weekEndDay)->format('Y-m-d');
            $wSet = $presensis->filter(fn ($p) => ($d = $p->tanggal->format('Y-m-d')) >= $wStart && $d <= $wEnd);
            $wTarget = $weekDays * 5;
            $wJamaah = $wSet->where('status', 'Jamaah')->count();
            $weekly[] = [
                'week' => $weekNo,
                'days' => $weekDays,
                'target' => $wTarget,
                'jamaah' => $wJamaah,
                'rating' => $wTarget > 0 ? round(($wJamaah / $wTarget) * 100) : 0,
            ];
            $weekStartDay += 7;
            $weekNo++;
        }

        $perWaktu = [];
        foreach (self::WAKTU as $w) {
            $set = $presensis->where('waktu_sholat', $w);
            $count = $set->count();
            $wJamaah = $set->where('status', 'Jamaah')->count();
            $wMasbuq = $set->where('status', 'Masbuq')->count();
            $wIzin = $set->where('status', 'Izin')->count();
            $wAlfa = $set->where('status', 'Alfa')->count();
            $wBelum = $set->count() > 0 ? max(0, $totalDays - $count) : $totalDays;
            $wScore = $count > 0 ? round(($wJamaah + ($wMasbuq * 0.5)) / $count * 100) : 0;
            $perWaktu[$w] = [
                'jamaah' => $wJamaah,
                'masbuq' => $wMasbuq,
                'izin' => $wIzin,
                'alfa' => $wAlfa,
                'belum' => $wBelum,
                'terisi' => $count,
                'score' => $wScore,
            ];
        }

        uasort($perWaktu, fn ($a, $b) => $a['score'] <=> $b['score']);

        $streak = $this->longestStreak($santri);

        $monthRank = null;
        // SATU query agregat untuk peringkat bulan ini. Sebelumnya looping semua
        // Santri aktif dan menjalankan COUNT per orang (N+1) -> hundreds of queries.
        $jamaahPerSantri = PresensiSholat::whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->where('status', 'Jamaah')
            ->groupBy('santri_id')
            ->select('santri_id', \DB::raw('COUNT(*) as c'))
            ->pluck('c', 'santri_id');

        $rankedQuery = Santri::query();
        if (\App\Support\SchemaCache::hasColumn('santris', 'status')) {
            $rankedQuery->where('status', 'Aktif');
        }
        $slotTotal = $totalDays * 5;
        $monthJamaah = $jamaahPerSantri->get($santri->id, 0);
        $ranked = $rankedQuery->get(['id'])
            ->map(function ($s) use ($jamaahPerSantri, $slotTotal) {
                $c = (int) $jamaahPerSantri->get($s->id, 0);
                $s->monthJamaah = $c;
                $s->monthRating = $slotTotal > 0 ? round(($c / $slotTotal) * 100) : 0;
                return $s;
            })
            ->sortByDesc('monthRating')
            ->values();
        $rankIdx = $ranked->search(fn ($s) => $s->id === $santri->id);
        if ($rankIdx !== false) {
            $monthRank = ['rank' => $rankIdx + 1, 'total' => $ranked->count(), 'jamaah' => (int) $monthJamaah];
        }

        return [
            'totalDays' => $totalDays,
            'seharusnya' => $seharusnya,
            'jamaah' => $jamaah,
            'masbuq' => $masbuq,
            'izin' => $izin,
            'alfa' => $alfa,
            'belumDiisi' => $belumDiisi,
            'terisi' => $terisi,
            'rating' => $rating,
            'level' => $level,
            'weekly' => $weekly,
            'perWaktu' => $perWaktu,
            'streak' => $streak,
            'rank' => $monthRank,
        ];
    }

    protected function longestStreak(Santri $santri): array
    {
        $dates = $santri->presensis()
            ->where('status', 'Jamaah')
            ->select('tanggal')
            ->distinct()
            ->orderBy('tanggal', 'asc')
            ->get()
            ->pluck('tanggal')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->all();

        if (count($dates) === 0) {
            return ['days' => 0, 'start' => null, 'end' => null];
        }

        $best = 1;
        $current = 1;
        $bestIdx = 0;
        $bestStart = 0;

        for ($i = 1; $i < count($dates); $i++) {
            $gap = (strtotime($dates[$i]) - strtotime($dates[$i - 1])) / 86400;
            if ($gap === 1) {
                $current++;
                if ($current > $best) {
                    $best = $current;
                    $bestStart = $i - $current + 1;
                }
            } else {
                $current = 1;
            }
        }

        return [
            'days' => $best,
            'start' => $dates[$bestStart],
            'end' => $dates[$bestStart + $best - 1],
        ];
    }

    protected function buildHistory(Santri $santri, string $tanggal = '', string $waktu = 'all')
    {
        $query = $santri->presensis()
            ->with('santri')
            ->orderBy('tanggal', 'desc');

        // FIELD() hanya ada di MySQL/MariaDB. Untuk driver lain (mis. sqlite saat
        // test) pakai CASE yang setara agar query tetap valid.
        if (\DB::connection()->getDriverName() === 'mysql') {
            $query->orderByRaw("FIELD(waktu_sholat, 'Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya')");
        } else {
            $query->orderByRaw("CASE waktu_sholat"
                . " WHEN 'Subuh' THEN 1 WHEN 'Dzuhur' THEN 2 WHEN 'Ashar' THEN 3"
                . " WHEN 'Maghrib' THEN 4 WHEN 'Isya' THEN 5 ELSE 6 END");
        }

        if ($tanggal) {
            $query->where('tanggal', $tanggal);
        }
        if ($waktu !== 'all') {
            $query->where('waktu_sholat', $waktu);
        }

        return $query->paginate(25)->withQueryString();
    }

    protected function buildTargets(Santri $santri, string $bulan): array
    {
        $start = \Carbon\Carbon::parse($bulan . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $totalDays = (int) $start->format('t');
        $seharusnya = $totalDays * 5;

        $jamaah = $santri->presensis()
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->where('status', 'Jamaah')
            ->count();

        $pct = $seharusnya > 0 ? round(($jamaah / $seharusnya) * 100) : 0;
        $target = 90;

        return [
            'target' => $target,
            'pct' => $pct,
            'jamaah' => $jamaah,
            'seharusnya' => $seharusnya,
            'achieved' => $pct >= $target,
            'progress' => min(100, round($pct / $target * 100)),
            'sisaJamaah' => max(0, (int) ceil($seharusnya * $target / 100) - $jamaah),
        ];
    }

    protected function emptyTargets(): array
    {
        return ['target' => 90, 'pct' => 0, 'jamaah' => 0, 'seharusnya' => 0, 'achieved' => false, 'progress' => 0, 'sisaJamaah' => 0];
    }
}
