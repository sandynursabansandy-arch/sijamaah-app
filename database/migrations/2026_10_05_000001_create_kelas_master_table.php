<?php

use App\Support\TahunAjar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kelas_master')) {
            Schema::create('kelas_master', function (Blueprint $table) {
                $table->id();
                $table->string('tahun_ajar', 20)->index();
                $table->string('nama', 100);
                $table->string('jenjang', 50)->nullable();
                $table->unsignedTinyInteger('tingkat')->nullable();
                $table->string('sisa', 50)->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();

                $table->unique(['tahun_ajar', 'nama'], 'kelas_master_tahun_nama_unique');
            });
        }

        if (!Schema::hasColumn('santris', 'kelas_master_id')) {
            Schema::table('santris', function (Blueprint $table) {
                $table->foreignId('kelas_master_id')
                    ->nullable()
                    ->after('kelas')
                    ->constrained('kelas_master')
                    ->nullOnDelete();
            });
        }

        $this->backfill();
    }

    public function down(): void
    {
        if (Schema::hasColumn('santris', 'kelas_master_id')) {
            Schema::table('santris', function (Blueprint $table) {
                $table->dropConstrainedForeignId('kelas_master_id');
            });
        }

        Schema::dropIfExists('kelas_master');
    }

    /**
     * Isi kelas_master dari nilai `kelas` yang sudah ada, lalu sambungkan
     * setiap santri. Data lama tidak diubah dan tidak ada downtime.
     */
    private function backfill(): void
    {
        $tahunAjar = TahunAjar::sekarang();

        if (!Schema::hasTable('santris')) {
            return;
        }

        // 1) Satu baris kelas_master per nilai kelas yang berbeda.
        $namaKelas = DB::table('santris')
            ->whereNotNull('kelas')
            ->where('kelas', '<>', '')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        foreach ($namaKelas as $nama) {
            $nama = trim((string) $nama);
            if ($nama === '') {
                continue;
            }

            $p = TahunAjar::parseNamaKelas($nama);

            $sudah = DB::table('kelas_master')
                ->where('tahun_ajar', $tahunAjar)
                ->where('nama', $nama)
                ->exists();

            if (!$sudah) {
                DB::table('kelas_master')->insert([
                    'tahun_ajar' => $tahunAjar,
                    'nama' => $nama,
                    'jenjang' => $p['jenjang'],
                    'tingkat' => $p['tingkat'],
                    'sisa' => $p['sisa'] !== '' ? $p['sisa'] : null,
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2) Sambungkan santris ke kelas_master berdasarkan nama kelas.
        // TRIM di kedua sisi supaya nilai kelas yang ada spasi tepi tetap
        // tertaut ke master yang namanya sudah dibersihkan di langkah 1.
        DB::statement(
            'UPDATE santris SET kelas_master_id = ('
            . ' SELECT km.id FROM kelas_master km'
            . ' WHERE km.tahun_ajar = ? AND km.nama = TRIM(santris.kelas)'
            . ' LIMIT 1'
            . ') WHERE kelas IS NOT NULL AND TRIM(kelas) <> ""'
            . ' AND kelas_master_id IS NULL',
            [$tahunAjar]
        );
    }
};
