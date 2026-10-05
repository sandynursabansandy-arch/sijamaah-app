<?php

namespace Tests\Feature;

use App\Models\IzinRequest;
use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Sweep seluruh halaman GET untuk memastikan tidak ada error 5xx.
 * Menangkap bug seperti "Undefined variable" yang membuat halaman 500.
 * Status 403/404 (akses memang dibatasi / data tidak ada) bukan kegagalan.
 */
class HalamanTanpaErrorTest extends TestCase
{
    use RefreshDatabase;

    protected function seedDasar(): array
    {
        $kamar = Kamar::create(['nama_kamar' => 'Kamar Uji']);
        $santri = Santri::create([
            'nama' => 'Santri Uji',
            'nis' => 'S001',
            'kamar_id' => $kamar->id,
            'kelas' => 'X',
        ]);
        $izin = IzinRequest::create([
            'santri_id' => $santri->id,
            'jenis' => 'Izin',
            'alasan' => 'Uji',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'status' => 'Menunggu',
        ]);

        return [$kamar, $santri, $izin];
    }

    protected function daftarUrl(): array
    {
        $daftar = [];
        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }
            $uri = $route->uri();
            if (strpos($uri, '{') !== false || strpos($uri, 'logout') !== false) {
                continue;
            }
            $daftar[] = '/' . ltrim($uri, '/');
        }

        return array_values(array_unique($daftar));
    }

    public function test_semua_halaman_tampil_tanpa_error_5xx(): void
    {
        [, $santri, $izin] = $this->seedDasar();
        $admin = User::factory()->create(['name' => 'Admin Uji', 'role' => 'admin']);
        $user = User::factory()->create(['name' => 'User Uji', 'role' => 'user']);

        $urls = array_merge($this->daftarUrl(), [
            '/santri/' . $santri->id . '/edit',
            '/izin/' . $izin->id . '/edit',
        ]);

        // Jaga agar test ini tetap bermakna bila daftar route berubah.
        $this->assertGreaterThanOrEqual(30, count($urls));

        foreach (['admin' => $admin, 'user' => $user, 'tamu' => null] as $peran => $aktor) {
            $gagal = [];

            foreach ($urls as $url) {
                $this->flushSession();
                $respons = $aktor ? $this->actingAs($aktor)->get($url) : $this->get($url);
                if ($respons->getStatusCode() >= 500) {
                    $gagal[$url] = $respons->getStatusCode();
                }
            }

            $this->assertSame([], $gagal, "Halaman 5xx sebagai {$peran}: " . json_encode($gagal));
        }
    }

    public function test_halaman_yang_gagal_tidak_menyembunyikan_penyebab(): void
    {
        [, $santri] = $this->seedDasar();
        $admin = User::factory()->create(['name' => 'Admin Uji', 'role' => 'admin']);

        $urls = array_merge($this->daftarUrl(), ['/santri/' . $santri->id . '/edit']);
        $rincian = [];

        foreach ($urls as $url) {
            $this->flushSession();
            $respons = $this->actingAs($admin)->get($url);
            if ($respons->getStatusCode() >= 500) {
                $this->withoutExceptionHandling();
                try {
                    $this->actingAs($admin)->get($url);
                } catch (\Throwable $e) {
                    $rincian[$url] = get_class($e) . ': ' . $e->getMessage();
                }
            }
        }

        $this->assertSame([], $rincian, 'Penyebab error 5xx: ' . json_encode($rincian));
    }
}
