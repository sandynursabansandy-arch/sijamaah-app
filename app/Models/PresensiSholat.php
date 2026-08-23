<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiSholat extends Model
{
    protected $fillable = ['santri_id', 'tanggal', 'waktu_sholat', 'status', 'catatan'];
    protected $casts = [
        'tanggal' => 'date',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }
}
