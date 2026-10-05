<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpanTanpaPersetujuanTest extends TestCase
{
    use RefreshDatabase;

    protected function payload(array $tambahan = []): array
    {
        return array_merge([
            'nama' => 'Santri Tanpa Consent',
            'kamar' => 'Kamar X',
            'kelas' => 'IX',
            'no_hp_wali' => '081234567890',
        ], $tambahan);
    }

    /** Tanpa mencentang persetujuan foto, data tetap harus tersimpan. */
    public function test_data_tersimpan_walaupun_kotak_persetujuan_foto_tidak_dicentang(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Consent', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post('/santri', $this->payload());

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('santri.create'));
        $this->assertDatabaseHas('santris', ['nama' => 'Santri Tanpa Consent']);
    }

    /** Update juga harus berhasil tanpa centang persetujuan. */
    public function test_perbaharui_tetap_berhasil_tanpa_persetujuan_foto(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Consent', 'role' => 'admin']);
        $kamar = Kamar::create(['nama_kamar' => 'Kamar X']);
        $santri = Santri::create([
            'nama' => 'Santri Lama',
            'nis' => 'S901',
            'kamar_id' => $kamar->id,
            'kelas' => 'IX',
        ]);

        $response = $this->actingAs($admin)->put('/santri/' . $santri->id, $this->payload(['nama' => 'Santri Baru']));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('santri.edit', $santri->id));
        $this->assertSame('Santri Baru', $santri->fresh()->nama);
    }
}