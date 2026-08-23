<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    protected $fillable = ['nama_kamar', 'wali_kamar', 'deskripsi'];

    public function santris()
    {
        return $this->hasMany(Santri::class);
    }
}