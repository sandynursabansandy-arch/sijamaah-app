<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Santri extends Model
{
    protected $fillable = ['nis', 'nama', 'kamar_id', 'kelas', 'jabatan', 'no_hp_wali', 'status'];

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(PresensiSholat::class);
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
