<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Filter halaman Data Santri:
 *  - pembeda jenis kelamin (Laki-laki / Perempuan);
 *  - dropdown filter hanya menampilkan nilai yang masih
 *    dipakai santri (kamar/kelas kosong disembunyikan);
 *  - filter tidak hilang setelah ke halaman edit/tambah
 *    santri lalu kembali ke daftar.
 */
class FilterSantriTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['name' => 'Admin Uji', 'role' => 'admin']);
    }

    protected function santri(string $nama, ?string $jenisKelamin = null, ?string $kamar = 'Kamar Uji'): Santri
    {
        return Santri::create([
            'nama' => $nama,
            'nis' => 'S' . crc32($nama),
            'kamar_id' => Kamar::firstOrCreate(['nama_kamar' => $kamar])->id,
            'kelas' => '-',
            'jenis_kelamin' => $jenisKelamin,
        ]);
    }

    public function test_filter_jenis_kelamin_memisahkan_daftar_santri(): void
    {
        $this->santri('Ahmad', 'Laki-laki');
        $this->santri('Budi', 'Laki-laki');
        $this->santri('Citra', 'Perempuan');

        $laki = $this->actingAs($this->admin)->get('/santri?filter_jenis_kelamin=Laki-laki');
        $laki->assertOk();
        $this->assertSame(2, $laki->viewData('santris')->total());

        $perempuan = $this->actingAs($this->admin)->get('/santri?filter_jenis_kelamin=Perempuan');
        $this->assertSame(1, $perempuan->viewData('santris')->total());

        // Nilai di luar dua pilihan yang ada diabaikan (tetap semua).
        $acak = $this->actingAs($this->admin)->get('/santri?filter_jenis_kelamin=lain');
        $this->assertSame(3, $acak->viewData('santris')->total());
    }

    public function test_template_import_ada_kolom_jenis_kelamin(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/santri-import/template?format=csv');

        $response->assertOk();
        // Download pakai stream, isi dibaca lewat streamedContent().
        $isi = method_exists($response, 'streamedContent')
            ? $response->streamedContent()
            : $response->getContent();

        $this->assertStringContainsString('Jenis Kelamin', $isi);
        // Urutan kolom: Nama, Rayon, Kelas Pengajian, Kelas Sekolah, ...
        // Jenis Kelamin di posisi terakhir supaya file tanpa header
        // (urutan baku lama) tetap terbaca dengan posisi yang sama.
        // fputcsv memberi tanda petik pada kolom yang mengandung koma.
        $this->assertStringContainsString(
            '"Nama Santri",Rayon/Kamar,"Kelas Pengajian","Kelas Sekolah"',
            $isi
        );
        $this->assertStringContainsString('"Kode Pos","Jenis Kelamin"', $isi);
    }

    public function test_import_csv_dengan_jenis_kelamin(): void
    {
        $csv = "Nama Santri,Rayon/Kamar,Jenis Kelamin,Kelas Pengajian\n"
            . "Citra,Rayon Baru,Perempuan,Takhosus I\n"
            . "Doni,Rayon Baru,Laki-laki,Takhosus I\n"
            . "Eko,Rayon Baru,L,Takhosus I\n"
            . "Fajar,Rayon Baru,wanita,Takhosus I\n"
            . "Gita,Rayon Baru,,-,Takhosus I\n";

        $berkas = UploadedFile::fake()->createWithContent('santri.csv', $csv);

        $response = $this->actingAs($this->admin)->post('/santri-import', [
            'file' => $berkas,
        ]);

        $response->assertSessionHas('success');
        $this->assertSame('Perempuan', Santri::where('nama', 'Citra')->first()->jenis_kelamin);
        $this->assertSame('Laki-laki', Santri::where('nama', 'Doni')->first()->jenis_kelamin);
        $this->assertSame('Laki-laki', Santri::where('nama', 'Eko')->first()->jenis_kelamin);
        $this->assertSame('Perempuan', Santri::where('nama', 'Fajar')->first()->jenis_kelamin);
        // Kolom kosong / tidak dikenal → null, baris tetap diimport.
        $this->assertNull(Santri::where('nama', 'Gita')->first()->jenis_kelamin);
    }

    public function test_form_filter_kosong_tidak_memecah_halaman(): void
    {
        // Submit form filter (GET) menyertakan SEMUA field,
        // termasuk yang kosong: ?search=&kamar_id=&filter=&...
        // Middleware ConvertEmptyStringsToNull mengubah nilai
        // kosong jadi null — halaman daftar tidak boleh error.
        $this->santri('Ahmad', 'Laki-laki');

        $response = $this->actingAs($this->admin)
            ->get('/santri?search=&kamar_id=&filter=&tahun_ajar='
                . '&kelas_id=&jenis_kelas=&kelas_pengajian_id='
                . '&kelas_sekolah_id=&filter_jenis_kelamin=');

        $response->assertOk();
        $this->assertSame(1, $response->viewData('santris')->total());
        $this->assertCount(1, $response->viewData('daftarKamarFilter'));
    }

    public function test_edit_dari_halaman_dua_kembali_ke_halaman_dua(): void
    {
        // 15 santri per halaman, jadi 20 santri → halaman 2 berisi 5.
        for ($i = 1; $i <= 20; $i++) {
            $this->santri("Santri " . str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'Laki-laki');
        }

        $response = $this->actingAs($this->admin)->get('/santri?page=2');
        $response->assertOk();

        // Link Edit di halaman 2 membawa page=2.
        $santriHalaman2 = Santri::orderBy('nama')->skip(15)->first();
        $response->assertSee(route('santri.edit', ['santri' => $santriHalaman2->id, 'page' => 2]));

        // Form edit menampilkan link kembali ke halaman 2.
        $response = $this->actingAs($this->admin)
            ->get('/santri/' . $santriHalaman2->id . '/edit?page=2');
        $response->assertSee(route('santri.index', ['page' => 2]));
        $response->assertSee('name="page" value="2"', false);

        // Simpan edit: redirect tetap ke form edit di halaman 2.
        $response = $this->actingAs($this->admin)
            ->put('/santri/' . $santriHalaman2->id, [
                'nama' => $santriHalaman2->nama,
                'kamar' => 'Kamar Uji',
                'jenis_kelamin' => 'Laki-laki',
                'page' => 2,
            ]);
        $response->assertRedirect(route('santri.edit', ['santri' => $santriHalaman2->id, 'page' => 2]));
    }

    public function test_dropdown_filter_rayon_hanya_menampilkan_kamar_terpakai(): void
    {
        Kamar::create(['nama_kamar' => 'Kamar Kosong']);
        $this->santri('Ahmad', 'Laki-laki', 'Kamar Terpakai');

        $response = $this->actingAs($this->admin)->get('/santri');

        $response->assertOk();
        $namaKamar = $response->viewData('daftarKamarFilter')->pluck('nama_kamar')->all();
        $this->assertContains('Kamar Terpakai', $namaKamar);
        $this->assertNotContains('Kamar Kosong', $namaKamar);
    }

    public function test_link_edit_dan_tambah_membawa_filter_sekarang(): void
    {
        $s = $this->santri('Ahmad', 'Laki-laki');

        $response = $this->actingAs($this->admin)
            ->get('/santri?search=Ah&filter_jenis_kelamin=Laki-laki');

        $response->assertOk();
        $response->assertSee(route('santri.edit', ['santri' => $s->id, 'search' => 'Ah', 'filter_jenis_kelamin' => 'Laki-laki']));
        $response->assertSee(route('santri.create', ['search' => 'Ah', 'filter_jenis_kelamin' => 'Laki-laki']));
    }

    public function test_halaman_edit_dan_simpan_tetap_memakai_filter_sebelumnya(): void
    {
        $s = $this->santri('Ahmad', 'Laki-laki');
        $kembali = ['search' => 'Ah', 'filter_jenis_kelamin' => 'Laki-laki'];

        // Form edit menampilkan link kembali + hidden field filter.
        $response = $this->actingAs($this->admin)
            ->get('/santri/' . $s->id . '/edit?' . http_build_query($kembali));

        $response->assertOk();
        $response->assertSee(route('santri.index', $kembali));
        $response->assertSee('name="search" value="Ah"', false);
        $response->assertSee('name="filter_jenis_kelamin" value="Laki-laki"', false);

        // Simpan perubahan: redirect kembali ke form edit dengan
        // parameter filter yang sama (tidak reset ke semua santri).
        $response = $this->actingAs($this->admin)
            ->put('/santri/' . $s->id, array_merge([
                'nama' => 'Ahmad Fauzi',
                'kamar' => 'Kamar Uji',
                'jenis_kelamin' => 'Laki-laki',
            ], $kembali));

        $response->assertRedirect(route('santri.edit', array_merge(['santri' => $s->id], $kembali)));
        $this->assertSame('Ahmad Fauzi', $s->fresh()->nama);
    }

    public function test_hapus_santri_tetap_memakai_filter_sebelumnya(): void
    {
        $s = $this->santri('Ahmad', 'Laki-laki');
        $kembali = ['filter_jenis_kelamin' => 'Laki-laki'];

        $response = $this->actingAs($this->admin)
            ->delete('/santri/' . $s->id, $kembali);

        $response->assertRedirect(route('santri.index', $kembali));
        $this->assertNull($s->fresh());
    }

    public function test_form_tambah_santri_menyimpan_jenis_kelamin(): void
    {
        $response = $this->actingAs($this->admin)->post('/santri', [
            'nama' => 'Citra',
            'kamar' => 'Kamar Uji',
            'jenis_kelamin' => 'Perempuan',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame('Perempuan', Santri::where('nama', 'Citra')->first()->jenis_kelamin);
    }
}
