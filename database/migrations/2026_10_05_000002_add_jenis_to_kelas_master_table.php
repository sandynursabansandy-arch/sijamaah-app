<?php

use App\Support\TahunAjar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan `jenis` ke kelas_master.
 *
 * Kelas pengajian (Kelas 1-6, Takhosus I/II) tidak naik tingkat tiap tahun,
 * sedangkan kelas sekolah ("1 SMP", "Ibtida 3") naik. Tanpa kolom ini,
 * "Buka Tahun Ajar Baru" akan menaikkan kelas pengajian dari 1 ke 2, ke 3,
 * dst. sampai habis — dan anak-anak ikut naik bersama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kelas_master') && !Schema::hasColumn('kelas_master', 'jenis')) {
            Schema::table('kelas_master', function (Blueprint $table) {
                $table->string('jenis', 10)
                    ->default(TahunAjar::JENIS_FORMAL)
                    ->after('sisa')
                    ->index();
            });
        }

        $this->backfill();
    }

    public function down(): void
    {
        if (Schema::hasColumn('kelas_master', 'jenis')) {
            Schema::table('kelas_master', function (Blueprint $table) {
                $table->dropColumn('jenis');
            });
        }
    }

    /**
     * Tandai kelas yang namanya cocok sebagai pengajian.
     *
     * Hanya menulis baris yang `jenis`-nya masih nilai default, jadi kelas
     * yang admin sudah tetapkan sebagai pengajian tetap. Baris yang sengaja
     * diubah ke formal masih bisa terdeteksi ulang kalau migrasi ini
     * dijalankan ulang — makanya di halaman /kelas selalu ada toggle jenis,
     * dan kelas yang salah tebak bisa dikoreksi di situ.
     */
    private function backfill(): void
    {
        if (!Schema::hasTable('kelas_master') || !Schema::hasColumn('kelas_master', 'jenis')) {
            return;
        }

        $rows = DB::table('kelas_master')->select(['id', 'nama'])->get();

        foreach ($rows as $row) {
            $jenis = TahunAjar::deteksiJenis((string) $row->nama);

            if ($jenis === TahunAjar::JENIS_FORMAL) {
                continue; // sudah default, tidak perlu menulis
            }

            DB::table('kelas_master')
                ->where('id', $row->id)
                ->where('jenis', TahunAjar::JENIS_FORMAL)
                ->update(['jenis' => $jenis, 'updated_at' => now()]);
        }
    }
};
