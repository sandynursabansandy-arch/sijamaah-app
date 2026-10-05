<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test: halaman utama admin harus bisa dirender (bukan 500).
 * Menangkap error view seperti "Undefined variable ..." sebelum deploy.
 */
class SmokeHalamanTest extends TestCase
{
    use RefreshDatabase;

    protected function seedDasar(): array
    {
        $kamar = Kamar::create(['nama_kamar' => 'Kamar Smoke']);
        $santri = Santri::create([
            'nama' => 'Santri Smoke',
            'nis' => 'S900',
            'kamar_id' => $kamar->id,
            'kelas' => 'X',
        ]);

        return [$kamar, $santri];
    }

    public function test_semua_halaman_admin_tampil_tanpa_error(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Smoke', 'role' => 'admin']);
        [, $santri] = $this->seedDasar();

        $urls = [
            '/',
            '/santri',
            '/santri/create',
            '/santri/' . $santri->id . '/edit',
            '/santri-export/cetak',
            '/santri-import',
            '/kamar',
            '/izin',
            '/kritik-saran',
            '/kritik-saran/kelola',
            '/pesan',
            '/panel-saya',
            '/notifikasi/daftar',
            '/riwayat',
            '/presensi/detail',
            '/rekap-berjamaah',
            '/rekap/cetak',
            '/ranking-alfa',
            '/ranking-berjamaah',
            '/input-cepat',
            '/profil',
            '/ganti-password',
            '/akun/cari',
        ];

        $gagal = [];
        // Samakan dengan produksi: warning PHP (undefined variable, dll) jadi exception.
        set_error_handler(function ($no, $str, $file = null, $line = null) {
            throw new \ErrorException($str, 0, $no, $file, $line);
        });

        try {
            foreach ($urls as $url) {
                $response = $this->actingAs($admin)->get($url);
                if ($response->getStatusCode() >= 500) {
                    $gagal[] = $url . ' => ' . $response->getStatusCode();
                }
            }
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $gagal, 'Halaman yang error: ' . implode(', ', $gagal));
    }

    /** Database kosong: banyak view punya blok @if yang membuat variabel undefined. */
    public function test_halaman_rekap_tampil_tanpa_data_santri(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Smoke Kosong', 'role' => 'admin']);

        $urls = ['/rekap-berjamaah', '/rekap/cetak', '/presensi/detail', '/ranking-berjamaah', '/ranking-alfa'];

        $gagal = [];
        set_error_handler(function ($no, $str, $file = null, $line = null) {
            throw new \ErrorException($str, 0, $no, $file, $line);
        });

        try {
            foreach ($urls as $url) {
                $response = $this->actingAs($admin)->get($url);
                if ($response->getStatusCode() >= 500) {
                    $gagal[] = $url . ' => ' . $response->getStatusCode();
                }
            }
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $gagal, 'Halaman yang error (data kosong): ' . implode(', ', $gagal));
    }
}