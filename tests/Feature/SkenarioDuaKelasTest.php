<?php

namespace Tests\Feature;

use App\Http\Controllers\SantriController;
use App\Models\KelasMaster;
use App\Models\Santri;
use App\Models\User;
use App\Support\TahunAjar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Skenario nyata yang diminta: satu Santri punya DUA kelas yang bergerak
 * dengan aturan BERBEDA.
 *
 * Contoh: Dzikra Zaedan selesai tahun ajaran di pengajian "Takhosus I" dan
 * sekolah "7 SMP".
 *   - Akhir tahun: kelas SEKOLAH naik  -> "8 SMP"
 *   - Kelas PENGAJIAN tidak naik      -> tetap "Takhosus I"
 *
 * Jadi format pengisian kelas harus dua bagian terpisah, dan wizard
 * "Buka Tahun Ajar Baru" hanya boleh memindahkan jalur sekolah.
 */
class SkenarioDuaKelasTest extends TestCase
{
    use RefreshDatabase;

    protected string $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tahun = TahunAjar::sekarang();
    }

    protected function admin(): User
    {
        return User::factory()->create(['name' => 'Admin', 'role' => 'admin']);
    }

    /** Isi form Tambah Santri persis seperti yang diketik admin di browser. */
    protected function simpanDzikra(array $tambahan = []): Santri
    {
        $response = $this->actingAs($this->admin())->post('/santri', array_merge([
            'nama' => 'Dzikra Zaedan',
            'kamar' => 'Kamar 1',
            'kelas_pengajian' => 'Takhosus I',
            'kelas_sekolah' => '7 SMP',
            'no_whatsapp' => '081234567890',
            'alamat_desa' => 'Sukamaju',
        ], $tambahan));

        $response->assertSessionHasNoErrors();

        return Santri::where('nama', 'Dzikra Zaedan')->firstOrFail();
    }

    // ------------------------------------------------------------------
    // 1) Format pengisian kelas memang dua bagian terpisah
    // ------------------------------------------------------------------

    public function test_form_tambah_santri_punya_dua_kolom_kelas_terpisah(): void
    {
        $html = $this->actingAs($this->admin())->get('/santri/create')->assertOk()->getContent();

        $this->assertStringContainsString('name="kelas_pengajian"', $html, 'Kolom Kelas Pengajian harus ada.');
        $this->assertStringContainsString('name="kelas_sekolah"', $html, 'Kolom Kelas Sekolah harus ada.');
        $this->assertStringNotContainsString('name="kelas"', $html, 'Kolom kelas tunggal harus hilang, diganti dua kolom.');
        $this->assertStringContainsString('Kelas Pengajian', $html);
        $this->assertStringContainsString('Jenjang Pendidikan', $html);
    }

    public function test_form_ubah_santri_juga_punya_dua_kolom_kelas_terpisah(): void
    {
        $d = $this->simpanDzikra();

        $html = $this->actingAs($this->admin())
            ->get('/santri/' . $d->id . '/edit')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="kelas_pengajian"', $html);
        $this->assertStringContainsString('name="kelas_sekolah"', $html);
        $this->assertStringNotContainsString('name="kelas"', $html);
        // Dua nilai awal harus terpisah, tidak ditimpa satu sama lain.
        $this->assertStringContainsString('value="Takhosus I"', $html);
        $this->assertStringContainsString('value="7 SMP"', $html);
    }

    public function test_template_impor_punya_dua_kolom_kelas(): void
    {
        $reflektor = new \ReflectionMethod(SantriController::class, 'kolomTemplateImport');
        $reflektor->setAccessible(true);

        $kolom = $reflektor->invoke(new SantriController);

        $this->assertContains('Kelas Pengajian', $kolom);
        $this->assertContains('Kelas Sekolah', $kolom);
        $this->assertNotContains('Kelas', $kolom, 'Kolom tunggal "Kelas" harus diganti dua kolom terpisah.');
    }

    // ------------------------------------------------------------------
    // 2) Dua kelas tersimpan terpisah
    // ------------------------------------------------------------------

    public function test_dua_kelas_tersimpan_terpisah(): void
    {
        $d = $this->simpanDzikra();

        $this->assertSame('Takhosus I', $d->kelas_pengajian);
        $this->assertSame('7 SMP', $d->kelas_sekolah);

        // Kolom lama `kelas` jadi cermin jalur sekolah supaya halaman &
        // ekspor lama tetap benar tanpa ditulis ulang.
        $this->assertSame('7 SMP', $d->kelas);

        // Masing-masing tertaut ke master kelas yang jenisnya benar.
        $pengajian = KelasMaster::where('nama', 'Takhosus I')->firstOrFail();
        $sekolah = KelasMaster::where('nama', '7 SMP')->firstOrFail();

        $this->assertSame('pengajian', $pengajian->jenis);
        $this->assertSame('formal', $sekolah->jenis);
        $this->assertSame($pengajian->id, $d->kelas_pengajian_id);
        $this->assertSame($sekolah->id, $d->kelas_sekolah_id);
    }

    public function test_hanya_satu_jalur_pun_boleh_diisi(): void
    {
        // Santri pondok, belum masuk sekolah formal.
        $d = $this->simpanDzikra(['kelas_sekolah' => '']);

        $this->assertSame('Takhosus I', $d->kelas_pengajian);
        $this->assertNull($d->kelas_sekolah);
        // Cermin otomatis jatuh ke jalur yang terisi.
        $this->assertSame('Takhosus I', $d->kelas);
    }

    // ------------------------------------------------------------------
    // 3) Akhir tahun: sekolah naik, pengajian TIDAK naik
    // ------------------------------------------------------------------

    public function test_akhir_tahun_kelas_sekolah_naik_tapi_pengajian_tetap(): void
    {
        $admin = $this->admin();
        $d = $this->simpanDzikra();

        $tahunBaru = TahunAjar::berikutnya($this->tahun);

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => $tahunBaru,
            'pindahkan_santri' => '1',
        ])->assertSessionHas('success');

        $d->refresh();

        // Kelas sekolah naik satu tingkat...
        $this->assertSame('8 SMP', $d->kelas_sekolah);
        $this->assertSame(
            KelasMaster::where('tahun_ajar', $tahunBaru)->where('nama', '8 SMP')->firstOrFail()->id,
            $d->kelas_sekolah_id
        );

        // ...tapi kelas pengajian TIDAK naik: masih Takhosus I.
        $this->assertSame('Takhosus I', $d->kelas_pengajian);
        $this->assertSame(
            KelasMaster::where('tahun_ajar', $tahunBaru)->where('nama', 'Takhosus I')->firstOrFail()->id,
            $d->kelas_pengajian_id
        );

        // Cermin `kelas` mengikuti jalur sekolah yang baru naik.
        $this->assertSame('8 SMP', $d->kelas);
    }

    public function test_pengajian_naik_hanya_bila_dipilih_manual(): void
    {
        $admin = $this->admin();
        $d = $this->simpanDzikra();
        $tahunBaru = TahunAjar::berikutnya($this->tahun);

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => $tahunBaru,
            'pindahkan_santri' => '1',
        ]);

        // Guru memutuskan pengajian "Takhosus I" -> "Takhosus II" sendiri,
        // jadi kelas tuju harus ada lebih dulu di tahun baru.
        KelasMaster::create([
            'tahun_ajar' => $tahunBaru,
            'nama' => 'Takhosus II',
            'jenis' => TahunAjar::JENIS_PENGAJIAN,
            'aktif' => true,
        ]);

        // Naik kelas dilakukan di tahun ajar yang sedang aktif: Dzikra kini ada
        // di "Takhosus I" tahun baru (lihat test_akhir_tahun_...).
        $asal = KelasMaster::where('tahun_ajar', $tahunBaru)->where('nama', 'Takhosus I')->firstOrFail();
        $tujuan = KelasMaster::where('tahun_ajar', $tahunBaru)->where('nama', 'Takhosus II')->firstOrFail();

        $this->actingAs($admin)->post('/kelas/naik', [
            'dari_kelas_id' => $asal->id,
            'ke_kelas_id' => $tujuan->id,
        ])->assertSessionHas('success');

        $d->refresh();

        // Hanya jalur pengajian yang berubah; jalur sekolah tetap 8 SMP.
        $this->assertSame('Takhosus II', $d->kelas_pengajian);
        $this->assertSame($tujuan->id, $d->kelas_pengajian_id);
        $this->assertSame('8 SMP', $d->kelas_sekolah);
    }

    public function test_tidak_bisa_naik_pengajian_ke_kelas_sekolah(): void
    {
        $admin = $this->admin();
        $d = $this->simpanDzikra();

        $asal = KelasMaster::where('nama', 'Takhosus I')->firstOrFail();
        $tujuan = KelasMaster::where('nama', '7 SMP')->firstOrFail();

        $this->actingAs($admin)->post('/kelas/naik', [
            'dari_kelas_id' => $asal->id,
            'ke_kelas_id' => $tujuan->id,
        ])->assertSessionHas('error');

        $d->refresh();
        $this->assertSame('Takhosus I', $d->kelas_pengajian);
        $this->assertSame('7 SMP', $d->kelas_sekolah);
    }

    // ------------------------------------------------------------------
    // 4) Tampilan & ekspor memakai dua kolom
    // ------------------------------------------------------------------

    public function test_daftar_santri_menampilkan_dua_kolom_kelas(): void
    {
        $this->simpanDzikra();

        $html = $this->actingAs($this->admin())->get('/santri')->assertOk()->getContent();

        $this->assertStringContainsString('Kelas Pengajian', $html);
        $this->assertStringContainsString('Jenjang Pendidikan', $html);
        $this->assertStringContainsString('Takhosus I', $html);
        $this->assertStringContainsString('7 SMP', $html);
    }

    public function test_filter_daftar_santri_pisah_per_jalur(): void
    {
        $this->simpanDzikra();

        $pengajian = KelasMaster::where('nama', 'Takhosus I')->firstOrFail();
        $sekolah = KelasMaster::where('nama', '7 SMP')->firstOrFail();

        $admin = $this->actingAs($this->admin());

        $cariPengajian = $admin->get('/santri?kelas_pengajian_id=' . $pengajian->id)->assertOk();
        $this->assertSame(1, $cariPengajian->viewData('santris')->total());

        $cariSekolah = $admin->get('/santri?kelas_sekolah_id=' . $sekolah->id)->assertOk();
        $this->assertSame(1, $cariSekolah->viewData('santris')->total());

        // Kedua jalur difilter sekaligus tetap menemukan orang yang sama.
        $duaJalur = $admin->get('/santri?kelas_pengajian_id=' . $pengajian->id . '&kelas_sekolah_id=' . $sekolah->id)->assertOk();
        $this->assertSame(1, $duaJalur->viewData('santris')->total());

        // Kelas sekolah yang tidak diikuti Dzikra tidak boleh ikut muncul.
        $lain = KelasMaster::create([
            'tahun_ajar' => $this->tahun,
            'nama' => '8 SMP',
            'jenis' => TahunAjar::JENIS_FORMAL,
            'aktif' => true,
        ]);

        $hanyaPengajian = $admin->get('/santri?kelas_pengajian_id=' . $pengajian->id)->assertOk();
        $this->assertSame(1, $hanyaPengajian->viewData('santris')->total());

        $kelasLain = $admin->get('/santri?kelas_sekolah_id=' . $lain->id)->assertOk();
        $this->assertSame(0, $kelasLain->viewData('santris')->total());
    }
}