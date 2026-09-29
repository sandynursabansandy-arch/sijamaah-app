<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PresensiAnalysis
{
    /**
     * Santri dengan alfa >= $minStreak hari beruntun (dihitung dari hari ini/kemarin).
     *
     * Satu query DISTINCT untuk daftar tanggal alfa + satu query untuk data Santri,
     * lalu streak dihitung dengan lookup array (sebelumnya memakai Eloquent model
     * dan `contains()` linear di dalam loop, yang mahal saat data sudah menumpuk).
     *
     * @return array<int, array{nama: string, kamar: string, streak: int}>
     */
    public static function alfaBeruntun(int $minStreak = 3): array
    {
        $since = Carbon::today()->subDays(30)->format('Y-m-d');

        $datesBySantri = DB::table('presensi_sholats')
            ->select('santri_id', 'tanggal')
            ->where('status', 'Alfa')
            ->where('tanggal', '>=', $since)
            ->distinct()
            ->orderBy('santri_id')
            ->get()
            ->groupBy('santri_id');

        if ($datesBySantri->isEmpty()) {
            return [];
        }

        $santriMap = DB::table('santris')
            ->leftJoin('kamars', 'kamars.id', '=', 'santris.kamar_id')
            ->whereIn('santris.id', $datesBySantri->keys()->all())
            ->select('santris.id as id', 'santris.nama as nama', 'kamars.nama_kamar as kamar')
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($datesBySantri as $santriId => $rows) {
            $dates = array_flip($rows->pluck('tanggal')->all());

            $cursor = Carbon::today();
            if (!isset($dates[$cursor->format('Y-m-d')])) {
                $cursor->subDay();
                if (!isset($dates[$cursor->format('Y-m-d')])) {
                    continue;
                }
            }

            $streak = 0;
            while (isset($dates[$cursor->format('Y-m-d')])) {
                $streak++;
                $cursor->subDay();
            }

            $santri = $santriMap->get($santriId);
            if ($streak >= $minStreak && $santri) {
                $result[] = [
                    'nama' => $santri->nama,
                    'kamar' => $santri->kamar ?? '-',
                    'streak' => $streak,
                ];
            }
        }

        usort($result, fn ($a, $b) => $b['streak'] <=> $a['streak']);

        return $result;
    }
}
