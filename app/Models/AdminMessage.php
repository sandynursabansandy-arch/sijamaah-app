<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminMessage extends Model
{
    protected $fillable = ['batch_id', 'santri_id', 'sent_by', 'pesan', 'is_read', 'read_at'];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}