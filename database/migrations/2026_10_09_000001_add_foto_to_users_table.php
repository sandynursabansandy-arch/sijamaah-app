<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nama file foto profil di storage/app/foto-profil/.
            // Null = belum ada foto, tampil inisial nama saja.
            $table->string('foto', 255)->nullable()->after('font_size');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
