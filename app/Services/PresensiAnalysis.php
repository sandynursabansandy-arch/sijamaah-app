<?php

namespace App\Services;

use App\Models\PresensiSholat;
use App\Models\Santri;
use Carbon\Carbon;

class PresensiAnalysis
{
    /**
     * Santri dengan alfa >= $minStreak hari beruntun (dihitung dari hari ini/kemarin).
     */
    public static function alfaBeruntun(int $minStreak = 3): array
    {
        $since = Carbon::now()->subDays(30)->format('Y-m-d');

        $dates = PresensiSholat::where('status', 'Alfa')
            ->where('tanggal', '>=', $since)
            ->select('santri_id', 'tanggal')
            ->distinct()
            ->get()
            ->groupBy('santri_id');

        $result = [];
        foreach ($dates as $santriId => $rows) {
            $streak = 0;
            $cursor = Carbon::today();

            $hasToday = $rows->contains(fn ($r) => $r->tanggal->isSameDay($cursor));
            if (!$hasToday) {
                $cursor->subDay();
                if (!$rows->contains(fn ($r) => $r->tanggal->isSameDay($cursor))) {
                    continue;
                }
            }

            while ($rows->contains(fn ($r) => $r->tanggal->isSameDay($cursor))) {
                $streak++;
                $cursor->subDay();
            }

            if ($streak >= $minStreak) {
                $santri = Santri::find($santriId);
                if ($santri) {
                    $result[] = [
                        'nama' => $santri->nama,
                        'kamar' => $santri->kamar?->nama_kamar ?? '-',
                        'streak' => $streak,
                    ];
                }
            }
        }

        usort($result, fn ($a, $b) => $b['streak'] <=> $a['streak']);
        return $result;
    }
}
