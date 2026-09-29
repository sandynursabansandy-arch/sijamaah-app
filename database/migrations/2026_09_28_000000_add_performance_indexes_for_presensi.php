<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index performa untuk query dashboard presensi.
 *
 * Latar belakang: aplikasi kadang "502 Bad Gateway" dari OpenResty karena
 * query agregasi presensi melakukan full table scan (belum ada index pada
 * `tanggal`), sehingga PHP-FPM kehabisan waktu / memori.
 */
return new class extends Migration
{
    /**
     * Index baru: [tabel, kolom, tipe]
     * Sengaja dibuat satu per satu dan dijaga idempoten supaya aman dijalankan ulang.
     */
    private function plan(): array
    {
        return [
            'ps_tanggal_idx'           => ['presensi_sholats', ['tanggal']],
            'ps_tanggal_waktu_idx'     => ['presensi_sholats', ['tanggal', 'waktu_sholat']],
            'ps_status_tanggal_idx'    => ['presensi_sholats', ['status', 'tanggal']],
            'santris_status_idx'       => ['santris', ['status']],
            'santris_kamar_status_idx' => ['santris', ['kamar_id', 'status']],
            'santris_nama_idx'         => ['santris', ['nama']],
            'izin_requests_status_idx' => ['izin_requests', ['status']],
        ];
    }

    public function up(): void
    {
        foreach ($this->plan() as $index => [$table, $columns]) {
            if (!Schema::hasTable($table) || $this->indexExists($table, $index)) {
                continue;
            }

            $existing = Schema::getColumnListing($table);
            $missing = array_diff($columns, $existing);
            if (!empty($missing)) {
                continue;
            }

            // Nama index harus diteruskan eksplisit, kalau tidak Laravel membuat
            // nama otomatis (mis. presensi_sholats_tanggal_index) sehingga
            // indexExists() selalu false dan index jadi dobel bila dijalankan ulang.
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $index) {
                $blueprint->index($columns, $index);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->plan() as $index => [$table]) {
            if (!Schema::hasTable($table) || !$this->indexExists($table, $index)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropIndex($index);
            });
        }
    }

    /**
     * Deteksi index lewat driver (SHOW INDEX), bukan information_schema,
     * karena information_schema sering dibatasi pada shared hosting.
     * Kalau tidak bisa dicek, dianggap sudah ada supaya tidak/dobel.
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $rows = DB::select('SHOW INDEX FROM `' . str_replace('`', '', $table) . '`');
        } catch (\Throwable $e) {
            return true;
        }

        foreach ($rows as $row) {
            $name = $row->Key_name ?? $row->name ?? null;
            if ($name === $index) {
                return true;
            }
        }

        return false;
    }
};
