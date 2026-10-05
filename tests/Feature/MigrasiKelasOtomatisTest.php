<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migrasi kelas dijalankan sendiri oleh halaman /kelas.
 *
 * Shared hosting tidak bisa `php artisan migrate` dan PHP di folder web
 * diblokir, jadi `KelasMasterController::pastikanTabelKelas()` menjalankan file
 * migrasinya sendiri begitu ada yang membuka /kelas. Di produksi itu berarti
 * TIDAK ADA yang menjalankan `artisan migrate` — semua bergantung pada kode ini.
 *
 * Karena itu harus diuji: halaman tetap bisa dibuka, dan migrasi yang
 * dijalankan ulang tidak merusak data yang sudah ada.
 */
class MigrasiKelasOtomatisTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_kelas_membuat_kolom_kapasitas_sendiri_bila_belum_ada()
    {
        $admin = User::factory()->create(['name' => 'Admin Uji', 'role' => 'admin']);

        // Simpan satu kelas pengajian dulu, lalu buang kolom kapasitas
        // supaya kondisinya sama dengan produksi yang belum pernah dimigrasi.
        $this->actingAs($admin)->post('/kelas', [
            'nama' => 'Takhosus I',
            'tahun_ajar' => now()->year . '/' . (now()->year + 1),
            'jenis' => 'pengajian',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(
            Schema::hasColumn('kelas_master', 'kapasitas'),
            'Kondisi awal uji: kolom kapasitas harus ada setelah dibuat lewat form.'
        );

        Schema::table('kelas_master', function ($table) {
            $table->dropColumn('kapasitas');
        });
        $this->assertFalse(Schema::hasColumn('kelas_master', 'kapasitas'), 'Kolom harus benar-benar hilang.');

        $this->actingAs($admin)->get('/kelas')->assertOk();

        $this->assertTrue(
            Schema::hasColumn('kelas_master', 'kapasitas'),
            'Halaman /kelas harus membuat sendiri kolom yang belum ada.'
        );
    }

    public function test_migrasi_ulang_mengisi_kapasitas_default_tanpa_menghapus_kelas()
    {
        $admin = User::factory()->create(['name' => 'Admin Uji', 'role' => 'admin']);
        $tahun = now()->year . '/' . (now()->year + 1);

        $this->actingAs($admin)->post('/kelas', [
            'nama' => 'Takhosus I',
            'tahun_ajar' => $tahun,
            'jenis' => 'pengajian',
        ]);
        $this->actingAs($admin)->post('/kelas', [
            'nama' => '7 SMP',
            'tahun_ajar' => $tahun,
            'jenis' => 'formal',
        ]);

        Schema::table('kelas_master', function ($table) {
            $table->dropColumn('kapasitas');
        });

        $this->actingAs($admin)->get('/kelas')->assertOk();

        $pengajian = \DB::table('kelas_master')->where('nama', 'Takhosus I')->first();
        $sekolah = \DB::table('kelas_master')->where('nama', '7 SMP')->first();

        $this->assertNotNull($pengajian, 'Kelas pengajian harus tetap ada setelah migrasi ulang.');
        $this->assertNotNull($sekolah, 'Kelas sekolah harus tetap ada setelah migrasi ulang.');
        $this->assertSame(20, (int) $pengajian->kapasitas, 'Kelas pengajian dapat patokan default.');
        $this->assertNull($sekolah->kapasitas, 'Kelas sekolah tidak punya jumlah ideal.');
    }
}