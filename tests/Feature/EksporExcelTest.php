<?php

namespace Tests\Feature;

use App\Http\Controllers\SantriController;
use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EksporExcelTest extends TestCase
{
    use RefreshDatabase;

    protected function seedData(): void
    {
        $kamar = Kamar::create(['nama_kamar' => 'Kamar Excel']);
        Santri::create([
            'nama' => 'Santi Excel',
            'nis' => 'S011',
            'kamar_id' => $kamar->id,
            'kelas' => 'XII',
            'alamat_jalan' => 'Jln. Excel',
            'alamat_lengkap' => 'Jln. Excel, Pasuruan',
        ]);
    }

    public function test_unduhan_excel_mengirim_berkas_xlsx(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Excel', 'role' => 'admin']);
        $this->seedData();

        $response = $this->actingAs($admin)->get(route('santri.export.excel'));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );
    }

    public function test_berkas_xlsx_valid_dan_memuat_data_santri(): void
    {
        $this->seedData();

        $path = storage_path('app/cek-export-test.xlsx');
        $controller = app(SantriController::class);
        $metode = new \ReflectionMethod($controller, 'tulisXlsx');
        $metode->setAccessible(true);
        $metode->invoke($controller, $path, ['No', 'Nama', 'Alamat'], [['1', 'Santi Excel', 'Jln. Excel, Pasuruan']]);

        $this->assertFileExists($path);
        $isi = (string) file_get_contents($path);

        $this->assertStringStartsWith('PK', $isi, 'Harus berupa arsip zip');
        $this->assertStringContainsString('PK' . "\x05\x06", $isi, 'Harus punya end-of-central-directory');
        $this->assertStringContainsString('PK' . "\x01\x02", $isi, 'Harus punya central directory');

        // Struktur zip harus konsisten: central directory langsung sebelum EOCD.
        $eocd = strrpos($isi, 'PK' . "\x05\x06");
        $jumlah = unpack('v', substr($isi, $eocd + 10, 2))[1];
        $ukuranCd = unpack('V', substr($isi, $eocd + 12, 4))[1];
        $offsetCd = unpack('V', substr($isi, $eocd + 16, 4))[1];
        $this->assertSame($eocd, $offsetCd + $ukuranCd, 'Offset central directory tidak konsisten');
        $this->assertSame('PK' . "\x01\x02", substr($isi, $offsetCd, 4));
        $this->assertSame(6, $jumlah, 'Workbook berisi 6 part');

        // Isi sheet DI-DEFLATE, jadi tidak bisa dibaca dari byte mentah zip.
        // Nama part memang tersimpan apa adanya di header zip, tapi teks di
        // dalamnya tidak — kalau diassert langsung ke $isi, test ini akan
        // gagal padahal file-nya benar. Jadi sheet-nya diekstrak dulu, dan
        // berkas baru dihapus SESUDAH semua assert selesai (file yang sudah
        // di-unlink tidak bisa lagi dibuka ZipArchive).
        $sheet = $this->bacaSheet($path);

        try {
            $this->assertStringContainsString('xl/worksheets/sheet1.xml', $isi);
            $this->assertStringContainsString('Santi Excel', $sheet);
            $this->assertStringContainsString('Jln. Excel, Pasuruan', $sheet);
            $this->assertStringContainsString('Alamat', $sheet);
        } finally {
            @unlink($path);
        }
    }

    /** Ekstrak satu entry zip dan kembalikan isinya sebagai teks. */
    protected function bacaSheet(string $path, string $entry = 'xl/worksheets/sheet1.xml'): string
    {
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($path) === true, 'Arsip zip harus bisa dibuka');
        $isi = $zip->getFromName($entry);
        $zip->close();

        $this->assertNotFalse($isi, 'Entry ' . $entry . ' harus ada di dalam zip');

        return (string) $isi;
    }
}