<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom jenis kelamin Santri (Laki-laki / Perempuan).
 *
 * Dipakai filter "Jenis Kelamin" di halaman Data Santri supaya
 * daftar bisa dibedakan per gender. Nullable: Santri lama yang
 * belum diisi gender tidak hilang dari daftar.
 *
 * Idempotent — boleh dijalankan ulang tanpa merusak data.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('santris')) {
            return;
        }

        if (!Schema::hasColumn('santris', 'jenis_kelamin')) {
            Schema::table('santris', function (Blueprint $table) {
                $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])
                    ->nullable()
                    ->after('nama');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('santris')) {
            return;
        }

        Schema::table('santris', function (Blueprint $table) {
            if (Schema::hasColumn('santris', 'jenis_kelamin')) {
                $table->dropColumn('jenis_kelamin');
            }
        });
    }
};
