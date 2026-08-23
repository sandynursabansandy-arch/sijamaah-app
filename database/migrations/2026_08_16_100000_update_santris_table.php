<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migration ini dibuat untuk pembersihan form, bukan untuk menghapus kolom
        // karena kolom status/no_hp_wali masih dibutuhkan dalam schema aplikasi.
        // Tidak melakukan perubahan agar tidak merusak database yang sudah aktif.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada perubahan yang perlu dibatalkan.
    }
};
