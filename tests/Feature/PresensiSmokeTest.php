<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\PresensiSholat;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test untuk perbaikan 502: dashboard tidak lagi memuat seluruh baris
 * presensi ke HTML, dan daftar nama diambil lewat endpoint lazy-load.
 */
class PresensiSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function makeData(): array
    {
        $kamar = Kamar::create(['nama_kamar' => 'Kamar A']);
        $santriA = Santri::create(['nis' => '001', 'nama' => 'Santri Alfa', 'kamar_id' => $kamar->id, 'kelas' => 'K', 'status' => 'Aktif']);
        $santriB = Santri::create(['nis' => '002', 'nama' => 'Santri Beta', 'kamar_id' => $kamar->id, 'kelas' => 'K', 'status' => 'Aktif']);
        $admin = User::factory()->create(['name' => 'Admin Test', 'role' => 'admin']);
        $pengasuh = User::factory()->create(['name' => 'Santri Alfa', 'role' => 'pengasuh']);

        return compact('kamar', 'santriA', 'santriB', 'admin', 'pengasuh');
    }

    protected function seedPresensi(int $days = 40): void
    {
        $santris = Santri::all();
        $waktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $status = ['Jamaah', 'Masbuq', 'Izin', 'Alfa'];

        foreach (range(1, $days) as $i) {
            $tanggal = now()->subDays($i)->format('Y-m-d');
            foreach ($santris as $s) {
                foreach ($waktu as $w) {
                    PresensiSholat::create([
                        'santri_id' => $s->id,
                        'tanggal' => $tanggal,
                        'waktu_sholat' => $w,
                        'status' => $status[($i + strlen((string) $w)) % 4],
                    ]);
                }
            }
        }
    }

    public function test_dashboard_renders_for_admin(): void
    {
        ['admin' => $admin] = $this->makeData();
        $this->seedPresensi();
        $this->actingAs($admin);

        $res = $this->get('/');
        $res->assertStatus(200);
        $html = $res->getContent();

        // Halaman utama tidak boleh lagi membawa daftar nama per status ( Causes payload membengkak).
        $this->assertStringNotContainsString('"names"', $html, 'Inline "names" seharusnya tidak lagi dikirim');
        fwrite(STDERR, "\n  [admin] dashboard " . strlen($html) . " bytes, peak " . round(memory_get_peak_usage(true) / 1048576, 1) . " MB\n");
    }

    public function test_dashboard_renders_for_pengasuh(): void
    {
        ['pengasuh' => $pengasuh] = $this->makeData();
        $this->seedPresensi();
        $this->actingAs($pengasuh);

        $res = $this->get('/');
        $res->assertStatus(200);
        fwrite(STDERR, "\n  [pengasuh] dashboard " . strlen($res->getContent()) . " bytes\n");
    }

    public function test_detail_endpoint_requires_auth(): void
    {
        $this->getJson('/presensi/detail?start=2026-07-01&end=2026-07-31')
            ->assertStatus(401);
    }

    public function test_detail_endpoint_returns_grouped_names(): void
    {
        ['admin' => $admin] = $this->makeData();
        $this->seedPresensi(3);
        $this->actingAs($admin);

        $start = now()->subDays(3)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $res = $this->getJson('/presensi/detail?start=' . $start . '&end=' . $end);
        $res->assertStatus(200)->assertJsonStructure(['names' => ['Hadir', 'Masbuq', 'Izin', 'Alfa']]);

        // Format grouped: { nama: [tanggal, ...] }
        $rows = 0;
        foreach (['Hadir', 'Masbuq', 'Izin', 'Alfa'] as $label) {
            foreach ($res->json('names.' . $label) as $nama => $tanggal) {
                $this->assertIsArray($tanggal);
                $rows += count($tanggal);
            }
        }
        // 3 hari x 2 santri x 5 waktu = 30 baris
        $this->assertSame(30, $rows);
        fwrite(STDERR, "\n  [detail] " . strlen($res->getContent()) . " bytes, {$rows} baris\n");
    }

    public function test_detail_endpoint_filters_by_waktu(): void
    {
        ['admin' => $admin] = $this->makeData();
        $this->seedPresensi(2);
        $this->actingAs($admin);

        $start = now()->subDays(2)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $res = $this->getJson('/presensi/detail?start=' . $start . '&end=' . $end . '&waktu=Subuh');
        $res->assertStatus(200);
        $rows = 0;
        foreach (['Hadir', 'Masbuq', 'Izin', 'Alfa'] as $label) {
            foreach ($res->json('names.' . $label) as $tanggal) {
                $rows += count($tanggal);
            }
        }
        // Hanya 1 waktu x 2 hari x 2 santri = 4 baris
        $this->assertSame(4, $rows);
    }

    public function test_detail_endpoint_rejects_bad_input(): void
    {
        ['admin' => $admin] = $this->makeData();
        $this->actingAs($admin);

        $this->getJson('/presensi/detail?start=abc')->assertStatus(422);
        $this->getJson('/presensi/detail?start=2026-01-01&end=2027-12-31')->assertStatus(422);
        $this->getJson('/presensi/detail?start=2026-08-01&end=2026-08-31&waktu=BOGUS')->assertStatus(422);
    }

    public function test_detail_endpoint_is_scoped_to_own_santri_for_pengasuh(): void
    {
        ['pengasuh' => $pengasuh, 'santriB' => $santriB] = $this->makeData();
        $this->seedPresensi(2);
        $this->actingAs($pengasuh);

        $start = now()->subDays(2)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $res = $this->getJson('/presensi/detail?start=' . $start . '&end=' . $end);
        $res->assertStatus(200);

        $names = [];
        foreach (['Hadir', 'Masbuq', 'Izin', 'Alfa'] as $label) {
            foreach (array_keys($res->json('names.' . $label)) as $nama) {
                $names[] = $nama;
            }
        }
        $this->assertNotContains('Santri Beta', $names, 'Pengasuh tidak boleh melihat nama Santri lain');
        $this->assertNotEmpty($names);
    }

    public function test_detail_endpoint_returns_empty_for_user_without_santri(): void
    {
        $user = User::factory()->create(['name' => 'Tanpa Santri', 'role' => 'pengasuh']);
        $this->actingAs($user);

        $start = now()->format('Y-m-d');
        $res = $this->getJson('/presensi/detail?start=' . $start . '&end=' . $start);
        $res->assertStatus(200);
        $this->assertSame([], $res->json('names'));
    }

    public function test_panel_saya_renders_and_computes_rank(): void
    {
        ['pengasuh' => $pengasuh] = $this->makeData();
        $this->seedPresensi(5);
        $this->actingAs($pengasuh);

        $queries = 0;
        \DB::listen(function () use (&$queries) { $queries++; });

        $res = $this->get('/panel-saya');
        $res->assertStatus(200);

        // Query harus tetap rendah: peringkat bulan dulu N+1 (satu COUNT per Santri).
        $this->assertLessThan(25, $queries, 'Terlalu banyak query di panel-saya (indikasi N+1 kembali)');
        fwrite(STDERR, "\n  [panel-saya] " . strlen($res->getContent()) . " bytes, {$queries} query\n");
    }
}
