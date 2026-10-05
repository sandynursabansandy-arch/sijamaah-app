<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah kolom `kapasitas` ke kelas_master.
 *
 * Kelas pengajian di pesantren menampung sekitar 20 orang per kelas: Kelas 1-5
 * dan Takhosus I/II. Angka ini adalah PATOKAN, bukan batas keras. Kalau semua
 * ada Santri yang belum layak naik, kelasnya boleh lewat dari 20 orang -
 * tidak ada yang otomatis diturunkan atau dipindah paksa. Gunanya hanya
 * membantu guru melihat kapan satu kelas sudah penuh.
 *
 * Kelas sekolah (7 SMP, 2 SMA, ...) tidak punya jumlah ideal, jadi `kapasitas`
 *nya dibiarkan NULL.
 */
return new class extends Migration
{
    /** Patokan jumlah orang per kelas pengajian. */
    private const DEFAULT_KAPASITAS = 20;

    public function up(): void
    {
        if (! Schema::hasTable('kelas_master')) {
            return;
        }

        if (! Schema::hasColumn('kelas_master', 'kapasitas')) {
            Schema::table('kelas_master', function (Blueprint $table) {
                $table->unsignedSmallInteger('kapasitas')->nullable()->after('jenis');
            });
        }

        // Hanya baris yang jenisnya kosong/terdeteksi pengajian yang diberi
        // patokan. Baris yang admin sudah isi sendiri tidak ditimpa.
        if (Schema::hasColumn('kelas_master', 'kapasitas')
            && Schema::hasColumn('kelas_master', 'jenis')) {
            DB::table('kelas_master')
                ->whereNull('kapasitas')
                ->where('jenis', 'pengajian')
                ->update(['kapasitas' => self::DEFAULT_KAPASITAS, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kelas_master') && Schema::hasColumn('kelas_master', 'kapasitas')) {
            Schema::table('kelas_master', function (Blueprint $table) {
                $table->dropColumn('kapasitas');
            });
        }
    }
};