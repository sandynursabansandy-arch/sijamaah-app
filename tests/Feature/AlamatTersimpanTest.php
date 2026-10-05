<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlamatTersimpanTest extends TestCase
{
    use RefreshDatabase;

    protected function alamat(array $tambahan = []): array
    {
        return array_merge([
            'nama' => 'Santri Alamat',
            'kamar' => 'Kamar Alamat',
            'kelas' => 'X',
            'no_hp_wali' => '081234567890',
            'alamat_provinsi' => 'Jawa Timur',
            'alamat_kabupaten' => 'Kota Pasuruan',
            'alamat_kecamatan' => 'Purmera',
            'alamat_desa' => 'Desa Sidoarjo',
            'alamat_dusun' => 'Dusun 1',
            'alamat_rt' => '03',
            'alamat_rw' => '01',
            'alamat_jalan' => 'Jln. Raya Sidoarjo',
            'alamat_nomor_rumah' => '45/B',
            'kode_pos' => '61252',
        ], $tambahan);
    }

    public function test_simpan_menyimpan_seluruh_bagian_alamat_lengkap(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Alamat', 'role' => 'admin']);

        $this->actingAs($admin)->post('/santri', $this->alamat())->assertSessionHasNoErrors();

        $santri = Santri::where('nama', 'Santri Alamat')->first();
        $this->assertNotNull($santri);
        $this->assertSame('Jln. Raya Sidoarjo', $santri->alamat_jalan);
        $this->assertSame('45/B', $santri->alamat_nomor_rumah);
        $this->assertSame('03', $santri->alamat_rt);
        $this->assertSame('Jawa Timur', $santri->alamat_provinsi);
        $this->assertSame('61252', $santri->kode_pos);

        $lengkap = (string) $santri->alamat_lengkap;
        foreach (['Jln. Raya Sidoarjo', '45/B', 'RT 03', 'RW 01', 'Dusun 1', 'Desa Sidoarjo', 'Purmera', 'Kota Pasuruan', 'Jawa Timur', '61252'] as $potongan) {
            $this->assertStringContainsString($potongan, $lengkap, 'alamat_lengkap harus memuat: ' . $potongan);
        }
    }

    public function test_perbaharui_memperbarui_alamat_lengkap(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Alamat', 'role' => 'admin']);
        $kamar = Kamar::create(['nama_kamar' => 'Kamar Alamat']);
        $santri = Santri::create([
            'nama' => 'Santri Alamat',
            'nis' => 'S777',
            'kamar_id' => $kamar->id,
            'kelas' => 'X',
        ]);

        $this->actingAs($admin)
            ->put('/santri/' . $santri->id, $this->alamat(['alamat_jalan' => 'Jln. Baru']))
            ->assertSessionHasNoErrors();

        $santri->refresh();
        $this->assertSame('Jln. Baru', $santri->alamat_jalan);
        $this->assertStringContainsString('Jln. Baru', (string) $santri->alamat_lengkap);
    }
}