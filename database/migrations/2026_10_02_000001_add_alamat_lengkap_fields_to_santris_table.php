<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            if (!Schema::hasColumn('santris', 'no_whatsapp')) {
                $table->string('no_whatsapp', 20)->nullable()->after('no_hp_wali');
            }

            if (!Schema::hasColumn('santris', 'alamat_provinsi')) {
                $table->string('alamat_provinsi', 50)->nullable()->after('jabatan');
            }
            if (!Schema::hasColumn('santris', 'alamat_kabupaten')) {
                $table->string('alamat_kabupaten', 50)->nullable()->after('alamat_provinsi');
            }
            if (!Schema::hasColumn('santris', 'alamat_kecamatan')) {
                $table->string('alamat_kecamatan', 50)->nullable()->after('alamat_kabupaten');
            }
            if (!Schema::hasColumn('santris', 'alamat_desa')) {
                $table->string('alamat_desa', 50)->nullable()->after('alamat_kecamatan');
            }
            if (!Schema::hasColumn('santris', 'alamat_dusun')) {
                $table->string('alamat_dusun', 50)->nullable()->after('alamat_desa');
            }
            if (!Schema::hasColumn('santris', 'alamat_rt')) {
                $table->string('alamat_rt', 5)->nullable()->after('alamat_dusun');
            }
            if (!Schema::hasColumn('santris', 'alamat_rw')) {
                $table->string('alamat_rw', 5)->nullable()->after('alamat_rt');
            }
            if (!Schema::hasColumn('santris', 'alamat_jalan')) {
                $table->string('alamat_jalan', 100)->nullable()->after('alamat_rw');
            }
            if (!Schema::hasColumn('santris', 'alamat_nomor_rumah')) {
                $table->string('alamat_nomor_rumah', 20)->nullable()->after('alamat_jalan');
            }
            if (!Schema::hasColumn('santris', 'kode_pos')) {
                $table->string('kode_pos', 10)->nullable()->after('alamat_nomor_rumah');
            }
            if (!Schema::hasColumn('santris', 'alamat_lengkap')) {
                $table->text('alamat_lengkap')->nullable()->after('kode_pos');
            }
            if (!Schema::hasColumn('santris', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('alamat_lengkap');
            }
            if (!Schema::hasColumn('santris', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('santris', 'foto_rumah')) {
                $table->string('foto_rumah', 255)->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $columns = [
                'no_whatsapp',
                'alamat_provinsi',
                'alamat_kabupaten',
                'alamat_kecamatan',
                'alamat_desa',
                'alamat_dusun',
                'alamat_rt',
                'alamat_rw',
                'alamat_jalan',
                'alamat_nomor_rumah',
                'kode_pos',
                'alamat_lengkap',
                'latitude',
                'longitude',
                'foto_rumah',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('santris', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
