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

    /**
     * Urutan rayon yang rapi untuk dropdown: "Rayon 1",
     * "Rayon 2", ..., "Rayon 8" (diurutkan menurut angka),
     * lalu nama lain (Pusaka, Saung Supa, dst.) secara
     * alfabetis. Dipakai supaya dropdown rayon selalu
     * berurutan, tidak bergantung urutan id atau alfabet.
     */
    public static function urutRayon($daftar): \Illuminate\Support\Collection
    {
        return $daftar->sortBy(function ($kamar) {
            $nama = trim((string) $kamar->nama_kamar);

            if (preg_match('/^rayon\s*(\d+)$/i', $nama, $m)) {
                return '0' . str_pad((int) $m[1], 5, '0', STR_PAD_LEFT);
            }

            return '1' . mb_strtolower($nama);
        })->values();
    }
}