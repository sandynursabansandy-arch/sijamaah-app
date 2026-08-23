<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiLog extends Model
{
    protected $fillable = [
        'user_id', 'santri_id', 'tanggal', 'waktu_sholat',
        'status_lama', 'status_baru', 'aksi',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
