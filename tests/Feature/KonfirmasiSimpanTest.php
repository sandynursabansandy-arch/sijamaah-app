<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KonfirmasiSimpanTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::factory()->create(['name' => 'Admin Konfirmasi', 'role' => 'admin']);
    }

    protected function payload(array $tambahan = []): array
    {
        return array_merge([
            'nama' => 'Santri Konfirmasi',
            'kamar' => 'Kamar Konfirmasi',
            'kelas' => 'VIII',
            'no_hp_wali' => '081234567890',
            'foto_rumah_consent' => '1',
        ], $tambahan);
    }

    /** Data baru -> judul popup "Sudah Disimpan". */
    public function test_simpan_data_baru_menampilkan_judul_sudah_disimpan(): void
    {
        $response = $this->actingAs($this->makeAdmin())
            ->post('/santri', $this->payload());

        $response->assertRedirect(route('santri.create'));
        $response->assertSessionHas('success_judul', 'Sudah Disimpan');
        $response->assertSessionHas('success', 'Data Santri berhasil disimpan.');
        $this->assertSame('Sudah Disimpan', session('success_judul'));
    }

    /** Data yang sudah ada lalu diubah -> judul popup "Sudah Diperbaharui". */
    public function test_perbaharui_data_eksisting_menampilkan_judul_sudah_diperbaharui(): void
    {
        $admin = $this->makeAdmin();
        $kamar = Kamar::create(['nama_kamar' => 'Kamar Konfirmasi']);
        $santri = Santri::create([
            'nama' => 'Santri Lama',
            'nis' => 'S900',
            'kamar_id' => $kamar->id,
            'kelas' => 'VIII',
        ]);

        $response = $this->actingAs($admin)
            ->put('/santri/' . $santri->id, $this->payload(['nama' => 'Santri Baru']));

        $response->assertRedirect(route('santri.edit', $santri->id));
        $response->assertSessionHas('success_judul', 'Sudah Diperbaharui');
        $response->assertSessionHas('success', 'Data Santri berhasil diperbaharui.');
        $this->assertSame('Santri Baru', $santri->fresh()->nama);
    }
}