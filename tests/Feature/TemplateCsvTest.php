<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateCsvTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['name' => 'Admin Template', 'role' => 'admin']);
    }

    public function test_template_csv_bisa_diunduh_dan_sesuai_format_import(): void
    {
        $response = $this->actingAs($this->admin())->get('/santri-import/template?format=csv');

        $response->assertOk();
        $isi = $response->streamedContent();

        $baris = array_values(array_filter(array_map('trim', explode("\n", $isi)), fn ($b) => $b !== ''));
        $this->assertStringContainsString('template_import_santri_', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('FORMULIR IMPORT', $baris[0]);
        $this->assertStringContainsString('Nama Santri', $baris[2]);
        $this->assertStringContainsString('Kode Pos', $baris[2]);

        $kolom = str_getcsv($baris[2], ',');
        $this->assertSame(18, count($kolom));
        $this->assertStringContainsString('nama', strtolower($kolom[0]));

        // Kelas dipecah jadi dua jalur supaya pengajian (tidak naik otomatis,
        // tidak semua orang naik) tidak tertukar dengan kelas sekolah.
        $this->assertStringContainsString('kelas pengajian', strtolower($kolom[2]));
        $this->assertStringContainsString('kelas sekolah', strtolower($kolom[3]));
        $this->assertStringContainsString('jenis kelamin', strtolower($kolom[17]));
    }

    public function test_template_excel_punya_judul_garis_dan_bisa_langsung_diimport(): void
    {
        Kamar::create(['nama_kamar' => 'Rayon 1']);

        $response = $this->actingAs($this->admin())->get('/santri-import/template');
        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));

        $path = storage_path('app/template-uji.xlsx');
        file_put_contents($path, $response->streamedContent());

        // Struktur workbook: harus punya sheet + style garis + judul yang di-merge.
        $reflektor = new \ReflectionClass(\App\Http\Controllers\SantriController::class);
        $controller = $reflektor->newInstanceWithoutConstructor();
        $parts = $reflektor->getMethod('bacaZip')->invoke($controller, $path);
        $this->assertArrayHasKey('xl/worksheets/sheet1.xml', $parts);
        $this->assertArrayHasKey('xl/styles.xml', $parts);
        $this->assertStringContainsString('borderId="1"', $parts['xl/styles.xml']);
        $this->assertStringContainsString('<border><left style="thin">', $parts['xl/styles.xml']);
        $this->assertStringContainsString('<c r="A1" s="3"', $parts['xl/worksheets/sheet1.xml']);
        $this->assertStringContainsString('<mergeCell ref="A1:R1"/>', $parts['xl/worksheets/sheet1.xml']);
        $this->assertStringContainsString('<c r="A4" s="2"/>', $parts['xl/worksheets/sheet1.xml']);
        $this->assertStringContainsString('<c r="A3" s="1"', $parts['xl/worksheets/sheet1.xml']);

        // Isi satu baris data, lalu import memakai file .xlsx tersebut.
        $rows = $reflektor->getMethod('bacaXlsx')->invoke($controller, $path);
        $this->assertStringContainsString('FORMULIR IMPORT', trim($rows[0][0]));
        $this->assertSame('Nama Santri', trim($rows[2][0]));
        $this->assertSame('Kelas Pengajian', trim($rows[2][2]));
        $this->assertSame('Kelas Sekolah', trim($rows[2][3]));
        $this->assertSame('Kode Pos', trim($rows[2][16]));
        $this->assertSame('Jenis Kelamin', trim($rows[2][17]));
        $this->assertCount(18, $rows[2]);

        $this->UbahSelXlsx($path, 4, 0, 'Ahmad Fauzi');
        $this->UbahSelXlsx($path, 4, 1, 'Rayon 1');
        $this->UbahSelXlsx($path, 4, 2, 'Takhosus I');
        $this->UbahSelXlsx($path, 4, 3, '7 SMP');
        $this->UbahSelXlsx($path, 4, 7, 'Jawa Timur');

        $import = $this->actingAs($this->admin())
            ->post('/santri-import', ['file' => new \Illuminate\Http\UploadedFile($path, 'template-uji.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true)]);
        $import->assertRedirect(route('santri.index'));
        $this->assertStringContainsString('1 santri ditambahkan', (string) session('success'));

        $santri = Santri::where('nama', 'Ahmad Fauzi')->first();
        $this->assertNotNull($santri);
        $this->assertSame('Rayon 1', $santri->kamar->nama_kamar);
        $this->assertSame('Jawa Timur', $santri->alamat_provinsi);

        // Dua jalur kelas tersimpan terpisah, masing-masing tertaut ke master
        // kelasnya sendiri yang jenisnya benar.
        $this->assertSame('Takhosus I', $santri->kelas_pengajian);
        $this->assertSame('7 SMP', $santri->kelas_sekolah);
        $this->assertTrue($santri->kelasPengajianMaster->isPengajian());
        $this->assertFalse($santri->kelasSekolahMaster->isPengajian());
        $this->assertNotSame($santri->kelas_pengajian_id, $santri->kelas_sekolah_id);

        @unlink($path);
    }

    public function test_import_tanpa_judul_kolom_tetap_dikenali(): void
    {
        $response = $this->actingAs($this->admin())->get('/santri-import/template?format=csv');
        $isi = $response->streamedContent();
        $baris = array_values(array_filter(array_map('trim', explode("\n", $isi)), fn ($b) => $b !== ''));

// Buang judul + keterangan, sisakan baris judul kolom dan satu data.
        $csv = $baris[2] . "\n" . 'Tanpa Judul,Rombel 2,1,7 SMP,Ustad,08123,08123,Jawa Barat,Kota Bandung,Cihampelas,Desa Y,Dusun 2,002,004,Jln. Asia,10,40151';
        $path = storage_path('app/tanpa-judul.csv');
        file_put_contents($path, "\xEF\xBB\xBF" . $csv);

        $import = $this->actingAs($this->admin())
            ->post('/santri-import', ['file' => new \Illuminate\Http\UploadedFile($path, 'tanpa-judul.csv', 'text/csv', null, true)]);
        $import->assertRedirect(route('santri.index'));
        $this->assertStringContainsString('1 santri ditambahkan', (string) session('success'));

        $santri = Santri::where('nama', 'Tanpa Judul')->first();
        $this->assertSame('Rombel 2', $santri->kamar->nama_kamar);
        $this->assertSame('1', $santri->kelas_pengajian);
        $this->assertSame('7 SMP', $santri->kelas_sekolah);

        @unlink($path);
    }

    /**
     * File versi lama: satu kolom "Kelas" saja, tanpa pemisahan jalur.
     *
     * Harus tetap bisa diimport, dan kolom tunggal itu ditempatkan ke jalur
     * yang benar menurut jenis kelasnya (bukan asal tempel ke salah satu).
     */
    public function test_import_file_lama_dengan_satu_kolom_kelas_masih_jalan(): void
    {
        $path = storage_path('app/file-lama.csv');
        file_put_contents($path, "\xEF\xBB\xBF" . implode("\n", [
            'Nama Santri,Rayon/Kamar,Kelas,Jabatan,No. HP Wali',
            'Santri Lama,Rayon Lama,Takhosus I,Ustad,08123',
            'Santri Lama Sekolah,Rayon Lama,7 SMP,Ustad,08123',
        ]));

        $this->actingAs($this->admin())
            ->post('/santri-import', ['file' => new \Illuminate\Http\UploadedFile($path, 'file-lama.csv', 'text/csv', null, true)])
            ->assertRedirect(route('santri.index'));

        $pengajian = Santri::where('nama', 'Santri Lama')->first();
        $this->assertSame('Takhosus I', $pengajian->kelas_pengajian);
        $this->assertNull($pengajian->kelas_sekolah);

        $sekolah = Santri::where('nama', 'Santri Lama Sekolah')->first();
        $this->assertSame('7 SMP', $sekolah->kelas_sekolah);
        $this->assertNull($sekolah->kelas_pengajian);

        @unlink($path);
    }

    /** Tulis nilai sel tertentu pada sheet1 workbook hasil unduhan. */
    protected function UbahSelXlsx(string $path, int $baris, int $kolom, string $nilai): void
    {
        $reflektor = new \ReflectionClass(\App\Http\Controllers\SantriController::class);
        $controller = $reflektor->newInstanceWithoutConstructor();
        $parts = $reflektor->getMethod('bacaZip')->invoke($controller, $path);

        $huruf = '';
        $sisa = $kolom;
        do {
            $huruf = chr(65 + ($sisa % 26)) . $huruf;
            $sisa = intdiv($sisa, 25) - 1;
        } while ($sisa >= 0);
        $ref = $huruf . $baris;

        $sel = '<c r="' . $ref . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($nilai, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            . '</t></is></c>';

        $sheet = preg_replace('/<c r="' . $ref . '"[^>]*\/>/', $sel, $parts['xl/worksheets/sheet1.xml']);
        $parts['xl/worksheets/sheet1.xml'] = $sheet;

        $writer = $reflektor->getMethod('tulisZip');
        $writer->invoke($controller, $path, $parts);
    }
}
