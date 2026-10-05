<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EksporFdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_unduhan_fdf_berakhiran_fdf_dan_berisi_data(): void
    {
        $admin = User::factory()->create(['name' => 'Admin FDF', 'role' => 'admin']);
        $kamar = Kamar::create(['nama_kamar' => 'Kamar FDF']);

        Santri::create([
            'nama' => 'Santri (FDF) \\uji',
            'nis' => 'S001',
            'kamar_id' => $kamar->id,
            'kelas' => 'VII',
            'alamat_jalan' => 'Jln. Raya Sidoarjo',
            'alamat_nomor_rumah' => '45/B',
        ]);

        $response = $this->actingAs($admin)->get(route('santri.export.fdf'));

        $response->assertOk();
        $disposisi = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $disposisi);
        $this->assertStringContainsString('.fdf"', $disposisi, 'Header Content-Disposition harus memuat ekstensi .fdf');
        $this->assertStringContainsString("filename*=UTF-8''", $disposisi);

        $isi = (string) $response->getContent();
        $this->assertStringStartsWith("%FDF-1.2\n%\xE2\xE3\xCF\xD3\n", $isi);
        $this->assertStringContainsString('/Fields [ 2 0 R', $isi, 'Field harus dirujuk sebagai objek tidak langsung');
        $this->assertStringContainsString("/Type /Annot\n/Subtype /Widget\n/FT /Tx", $isi);
        $this->assertStringContainsString('/T (nama_1)', $isi);
        $this->assertStringContainsString('Santri \\(FDF\\) \\\\uji', $isi, 'Kurung & backslash harus di-escape');
        $this->assertStringContainsString('/ID [ <', $isi, 'Trailer FDF asli memiliki /ID');
        $this->assertStringEndsWith("%%EOF\n", $isi);
    }

    /** Nama beraksen/non-ASCII ditulis sebagai hex string UTF-16BE. */
    public function test_nilai_non_ascii_ditulis_sebagai_hex_utf16(): void
    {
        $admin = User::factory()->create(['name' => 'Admin FDF', 'role' => 'admin']);
        $kamar = Kamar::create(['nama_kamar' => 'Kamar FDF']);
        Santri::create([
            'nama' => 'Santi',
            'nis' => 'S002',
            'kamar_id' => $kamar->id,
            'kelas' => 'VII',
            'alamat_desa' => 'Désa',
        ]);

        $isi = (string) $this->actingAs($admin)->get(route('santri.export.fdf'))->getContent();
        $this->assertStringContainsString('/V <FEFF', $isi, 'Nilai non-ASCII harus jadi hex string UTF-16BE');
        // "Désa" -> BOM FEFF + 0044 00E9 0073 0061
        $this->assertStringContainsString('FEFF004400E900730061', $isi);
    }
}
