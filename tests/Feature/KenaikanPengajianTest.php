<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\KelasMaster;
use App\Models\Santri;
use App\Models\User;
use App\Support\TahunAjar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman "Kelas & Tahun Ajar" untuk jalur pengajian.
 *
 * Dua kebutuhan nyata di pesantren:
 *
 * 1. Kelas pengajian (Kelas 1-5, Takhosus I/II, ±20 orang per kelas) TIDAK
 *    mengikuti kenaikan tahun ajaran. Orang naik hanya kalau guru sudah menilai
 *    paham dan layak — jadi kenaikan harus bisa per orang, bukan per kelas.
 *
 * 2. Kelas sekolah (7 SMP, 2 SMA) naik otomatis. Keduanya harus terpisah jelas
 *    supaya tidak saling tertukar:DZIKRA Zaedan boleh sekolahnya 2 SMA
 *    sementara pengajiannya masih Kelas 2.
 */
class KenaikanPengajianTest extends TestCase
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

    protected function kelasPengajian(string $nama, ?int $kapasitas = null): KelasMaster
    {
        $ada = KelasMaster::where('tahun_ajar', $this->tahun)->where('nama', $nama)->first();

        if ($ada) {
            return $ada;
        }

        return KelasMaster::create([
            'tahun_ajar' => $this->tahun,
            'nama' => $nama,
            'jenis' => KelasMaster::PENGAJIAN,
            'kapasitas' => $kapasitas,
            'aktif' => true,
        ]);
    }

    protected function kelasSekolah(string $nama): KelasMaster
    {
        $ada = KelasMaster::where('tahun_ajar', $this->tahun)->where('nama', $nama)->first();

        if ($ada) {
            return $ada;
        }

        $p = TahunAjar::parseNamaKelas($nama);

        return KelasMaster::create([
            'tahun_ajar' => $this->tahun,
            'nama' => $nama,
            'jenjang' => $p['jenjang'],
            'tingkat' => $p['tingkat'],
            'jenis' => KelasMaster::FORMAL,
            'aktif' => true,
        ]);
    }

    /** Santri dengan dua jalur kelas sekaligus. */
    protected function buatSantri(
        string $nama,
        ?string $pengajian = null,
        ?string $sekolah = null
    ): Santri {
        $pengajianId = $pengajian ? $this->kelasPengajian($pengajian)->id : null;
        $sekolahId = $sekolah ? $this->kelasSekolah($sekolah)->id : null;

        return Santri::create([
            'nama' => $nama,
            'nis' => 'S'.crc32($nama),
            'kamar_id' => Kamar::firstOrCreate(['nama_kamar' => 'Kamar 1'])->id,
            'kelas_pengajian' => $pengajian,
            'kelas_pengajian_id' => $pengajianId,
            'kelas_sekolah' => $sekolah,
            'kelas_sekolah_id' => $sekolahId,
            // Cermin `kelas` ikut jalur sekolah, sama seperti form aplikasi.
            'kelas' => $sekolah ?: $pengajian,
            'kelas_master_id' => $sekolahId ?: $pengajianId,
        ]);
    }

    // ------------------------------------------------------------------
    // 1) Dua jalur terpisah di halaman /kelas
    // ------------------------------------------------------------------

    public function test_halaman_kelas_menampilkan_dua_daftar_terpisah(): void
    {
        $this->kelasPengajian('Takhosus I');
        $this->kelasPengajian('Takhosus II');
        $this->kelasSekolah('2 SMA');
        $this->buatSantri('Dzikra Zaedan', 'Takhosus I', '2 SMA');

        $html = $this->actingAs($this->admin())->get('/kelas')->assertOk()->getContent();

        $this->assertStringContainsString('Kelas Pengajian', $html);
        $this->assertStringContainsString('Kelas Sekolah', $html);
        $this->assertStringContainsString('Kenaikan Kelas Pengajian', $html);

        // Kelas sekolah harus punya kolom "Kelas Berikutnya"; pengajian tidak,
        // karena memang tidak ada tingkat berikutnya yang pasti.
        $this->assertStringContainsString('Kelas Berikutnya', $html);
        $this->assertStringContainsString('3 SMA', $html, 'Kelas sekolah harus menampilkan saran kelas berikutnya.');
    }

    public function test_jumlah_anggota_kelas_pengajian_bukan_dihitung_dari_jalur_sekolah(): void
    {
        $takh1 = $this->kelasPengajian('Takhosus I');
        $takh2 = $this->kelasPengajian('Takhosus II');
        $sma = $this->kelasSekolah('2 SMA');

        // 4 orang di pengajian, dan mereka juga punya kelas sekolah yang sama.
        for ($i = 1; $i <= 4; $i++) {
            $this->buatSantri('Pengajian ' . $i, 'Takhosus I', '2 SMA');
        }
        $this->buatSantri('Sekolah saja', null, '2 SMA');

        $response = $this->actingAs($this->admin())->get('/kelas')->assertOk();

        $this->assertSame(4, $takh1->fresh()->jumlahSantri());
        $this->assertSame(0, $takh2->fresh()->jumlahSantri());
        $this->assertSame(5, $sma->fresh()->jumlahSantri());

        $this->assertSame(4, $response->viewData('jumlahPengajian'));
        $this->assertSame(5, $response->viewData('jumlahSekolah'));
        $this->assertCount(2, $response->viewData('kelasPengajian'));
        $this->assertCount(1, $response->viewData('kelasSekolah'));
    }

    // ------------------------------------------------------------------
    // 2) Urutan kelas pengajian
    // ------------------------------------------------------------------

    public function test_kelas_pengajian_diurutkan_dari_yang_paling_rendah(): void
    {
        foreach (['Takhosus II', '5', '1', 'Takhosus I', '3'] as $nama) {
            $this->kelasPengajian($nama);
        }

        $response = $this->actingAs($this->admin())->get('/kelas')->assertOk();

        $urut = $response->viewData('kelasPengajian')->pluck('nama')->all();

        $this->assertSame(['1', '3', '5', 'Takhosus I', 'Takhosus II'], $urut);
    }

    // ------------------------------------------------------------------
    // 3) Kapasitas ±20 orang
    // ------------------------------------------------------------------

    public function test_kapasitas_default_kelas_pengajian_sebanyak_20(): void
    {
        $k = $this->kelasPengajian('Kelas 1');

        $this->assertSame(20, $k->kapasitasEfektif());
    }

    public function test_kapasitas_kosong_sTetap_dipakai_sebagai_patokan(): void
    {
        $k = $this->kelasPengajian('Kelas 1');
        for ($i = 1; $i <= 3; $i++) {
            $this->buatSantri('Murid ' . $i, 'Kelas 1');
        }

        $this->assertSame(17, $k->fresh()->sisaKursi());
        $this->assertFalse($k->sudahPenuh());
    }

    public function test_kelas_sekolah_tidak_punya_patokan_jumlah_orang(): void
    {
        $k = $this->kelasSekolah('2 SMA');

        $this->assertNull($k->kapasitasEfektif());
        $this->assertNull($k->sisaKursi());
        $this->assertFalse($k->sudahPenuh());
    }

    public function test_kapasitas_bisa_disisi_dari_halaman_kelas(): void
    {
        $k = $this->kelasPengajian('Kelas 1');

        $this->actingAs($this->admin())
            ->put('/kelas/' . $k->id, ['nama' => 'Kelas 1', 'kapasitas' => 24])
            ->assertSessionHas('success');

        $this->assertSame(24, $k->fresh()->kapasitas);
        $this->assertSame(24, $k->fresh()->kapasitasEfektif());
    }

    public function test_kapasitas_kelas_sekolah_diabaikan(): void
    {
        $k = $this->kelasSekolah('2 SMA');

        $this->actingAs($this->admin())
            ->put('/kelas/' . $k->id, ['nama' => '2 SMA', 'kapasitas' => 20]);

        $this->assertNull($k->fresh()->kapasitas);
    }

    public function test_kapasitas_baru_default_ke_20_untuk_kelas_pengajian(): void
    {
        $this->actingAs($this->admin())->post('/kelas', [
            'nama' => 'Takhosus I',
            'tahun_ajar' => $this->tahun,
        ])->assertSessionHas('success');

        $k = KelasMaster::where('nama', 'Takhosus I')->firstOrFail();

        $this->assertSame(KelasMaster::PENGAJIAN, $k->jenis);
        $this->assertSame(20, $k->kapasitasEfektif());
    }

    // ------------------------------------------------------------------
    // 4) Kenaikan per orang
    // ------------------------------------------------------------------

    public function test_hanya_santri_yang_dicentang_yang_naik(): void
    {
        $takh1 = $this->kelasPengajian('Takhosus I');
        $takh2 = $this->kelasPengajian('Takhosus II');

        $naik = $this->buatSantri('Benaar Naik', 'Takhosus I');
        $belum = $this->buatSantri('Belum Layak', 'Takhosus I');
        $tetap = $this->buatSantri('Juga Tetap', 'Takhosus I');

        $this->actingAs($this->admin())
            ->post('/kelas/naik-pengajian', [
                'dari_kelas_id' => $takh1->id,
                'ke_kelas_id' => $takh2->id,
                'santri_ids' => [$naik->id, $tetap->id],
            ])
            ->assertSessionHas('success');

        // Yang dicentang naik...
        $this->assertSame('Takhosus II', $naik->fresh()->kelas_pengajian);
        $this->assertSame($takh2->id, $naik->fresh()->kelas_pengajian_id);

        $this->assertSame('Takhosus II', $tetap->fresh()->kelas_pengajian);

        // ...yang tidak dicentang TETAP di kelas asalnya.
        $this->assertSame('Takhosus I', $belum->fresh()->kelas_pengajian);
        $this->assertSame($takh1->id, $belum->fresh()->kelas_pengajian_id);

        $this->assertSame(1, $takh1->fresh()->jumlahSantri());
        $this->assertSame(2, $takh2->fresh()->jumlahSantri());
    }

    public function test_kenaikan_pengajian_tidak_menyentuh_kelas_sekolah(): void
    {
        $takh1 = $this->kelasPengajian('Takhosus I');
        $takh2 = $this->kelasPengajian('Takhosus II');

        // Kasus yang disebut: sekolah sudah 2 SMA, pengajian masih Kelas 1.
        $d = $this->buatSantri('Dzikra Zaedan', 'Kelas 1', '2 SMA');
        $sma = KelasMaster::where('nama', '2 SMA')->firstOrFail();
        $kelas1 = $this->kelasPengajian('Kelas 1');
        $kelas2 = $this->kelasPengajian('Kelas 2');

        $this->actingAs($this->admin())
            ->post('/kelas/naik-pengajian', [
                'dari_kelas_id' => $kelas1->id,
                'ke_kelas_id' => $kelas2->id,
                'santri_ids' => [$d->id],
            ])
            ->assertSessionHas('success');

        $d->refresh();

        // Hanya jalur pengajian yang berubah.
        $this->assertSame('Kelas 2', $d->kelas_pengajian);
        $this->assertSame('2 SMA', $d->kelas_sekolah, 'Kelas sekolah tidak boleh ikut naik.');
        $this->assertSame($sma->id, $d->kelas_sekolah_id);
        $this->assertSame('2 SMA', $d->kelas, 'Kolom cermin ikut jalur sekolah.');
        $this->assertSame($sma->id, $d->kelas_master_id);
    }

    public function test_kenaikan_pengajian_ditolak_ke_kelas_sekolah(): void
    {
        $takh1 = $this->kelasPengajian('Takhosus I');
        $sma = $this->kelasSekolah('2 SMA');
        $s = $this->buatSantri('Dzikra Zaedan', 'Takhosus I', '2 SMA');

        $this->actingAs($this->admin())
            ->post('/kelas/naik-pengajian', [
                'dari_kelas_id' => $takh1->id,
                'ke_kelas_id' => $sma->id,
                'santri_ids' => [$s->id],
            ])
            ->assertSessionHas('error');

        $s->refresh();
        $this->assertSame('Takhosus I', $s->kelas_pengajian);
        $this->assertSame('2 SMA', $s->kelas_sekolah);
    }

    public function test_kenaikan_ditolak_bila_santri_bukan_anggota_kelas_asal(): void
    {
        $takh1 = $this->kelasPengajian('Takhosus I');
        $takh2 = $this->kelasPengajian('Takhosus II');
        $takh3 = $this->kelasPengajian('Takhosus III');

        $seorangLain = $this->buatSantri('Santri Kelas Tiga', 'Takhosus III');

        $this->actingAs($this->admin())
            ->post('/kelas/naik-pengajian', [
                'dari_kelas_id' => $takh1->id,
                'ke_kelas_id' => $takh2->id,
                'santri_ids' => [$seorangLain->id],
            ])
            ->assertSessionHas('error');

        // Ditolak seluruhnya, bukan dipindah separuh.
        $this->assertSame('Takhosus III', $seorangLain->fresh()->kelas_pengajian);
        $this->assertSame(0, $takh2->fresh()->jumlahSantri());
    }

    public function test_kenaikan_ditolak_bila_tidak_ada_yang_dicentang(): void
    {
        $takh1 = $this->kelasPengajian('Takhosus I');
        $takh2 = $this->kelasPengajian('Takhosus II');

        $this->actingAs($this->admin())
            ->post('/kelas/naik-pengajian', [
                'dari_kelas_id' => $takh1->id,
                'ke_kelas_id' => $takh2->id,
                'santri_ids' => [],
            ])
            ->assertSessionHasErrors('santri_ids');
    }

    public function test_kelas_tujuan_yang_melewati_patokan_tetap_dijalankan(): void
    {
        $takh1 = $this->kelasPengajian('Takhosus I');
        $takh2 = $this->kelasPengajian('Takhosus II', 2);

        $chosen = [];
        for ($i = 1; $i <= 4; $i++) {
            $chosen[] = $this->buatSantri('Murid ' . $i, 'Takhosus I')->id;
        }

        $this->actingAs($this->admin())
            ->post('/kelas/naik-pengajian', [
                'dari_kelas_id' => $takh1->id,
                'ke_kelas_id' => $takh2->id,
                'santri_ids' => $chosen,
            ])
            ->assertSessionHas('success');

        // Berjalan, karena "penuh" di pengajian bukan error: artinya masih ada
        // yang belum naik.
        $this->assertSame(4, $takh2->fresh()->jumlahSantri());
        $this->assertStringContainsString('lewat patokan', (string) session('success'));
    }

    // ------------------------------------------------------------------
    // 4b) Penurunan / pembatalan kenaikan pengajian (salah input)
    // ------------------------------------------------------------------

    public function test_turunkan_pengajian_mengembalikan_santri_ke_kelas_asal(): void
    {
        $kelas1 = $this->kelasPengajian('Kelas 1');
        $kelas2 = $this->kelasPengajian('Kelas 2');

        $s = $this->buatSantri('Salah Naik', 'Kelas 1');

        // Naik dulu ke Kelas 2...
        $this->actingAs($this->admin())
            ->post('/kelas/naik-pengajian', [
                'dari_kelas_id' => $kelas1->id,
                'ke_kelas_id' => $kelas2->id,
                'santri_ids' => [$s->id],
            ])
            ->assertSessionHas('success');

        $this->assertSame('Kelas 2', $s->fresh()->kelas_pengajian);

        // ...lalu dikembalikan ke Kelas 1 karena salah input.
        $this->actingAs($this->admin())
            ->post('/kelas/turunkan-pengajian', [
                'dari_kelas_id' => $kelas2->id,
                'ke_kelas_id' => $kelas1->id,
                'santri_ids' => [$s->id],
            ])
            ->assertSessionHas('success');

        $s->refresh();
        $this->assertSame('Kelas 1', $s->kelas_pengajian);
        $this->assertSame($kelas1->id, $s->kelas_pengajian_id);
        $this->assertSame(1, $kelas1->fresh()->jumlahSantri());
        $this->assertSame(0, $kelas2->fresh()->jumlahSantri());
        $this->assertStringContainsString('dikembalikan', (string) session('success'));
    }

    public function test_turunkan_pengajian_tidak_menyentuh_kelas_sekolah(): void
    {
        $kelas1 = $this->kelasPengajian('Kelas 1');
        $kelas2 = $this->kelasPengajian('Kelas 2');
        $sma = $this->kelasSekolah('2 SMA');

        $d = $this->buatSantri('Dzikra Zaedan', 'Kelas 2', '2 SMA');

        $this->actingAs($this->admin())
            ->post('/kelas/turunkan-pengajian', [
                'dari_kelas_id' => $kelas2->id,
                'ke_kelas_id' => $kelas1->id,
                'santri_ids' => [$d->id],
            ])
            ->assertSessionHas('success');

        $d->refresh();
        $this->assertSame('Kelas 1', $d->kelas_pengajian);
        $this->assertSame('2 SMA', $d->kelas_sekolah, 'Kelas sekolah tidak boleh ikut turun.');
        $this->assertSame($sma->id, $d->kelas_sekolah_id);
        $this->assertSame('2 SMA', $d->kelas, 'Kolom cermin ikut jalur sekolah.');
        $this->assertSame($sma->id, $d->kelas_master_id);
    }

    public function test_turunkan_pengajian_ditolak_ke_kelas_sekolah(): void
    {
        $kelas2 = $this->kelasPengajian('Kelas 2');
        $sma = $this->kelasSekolah('2 SMA');
        $s = $this->buatSantri('Dzikra Zaedan', 'Kelas 2', '2 SMA');

        $this->actingAs($this->admin())
            ->post('/kelas/turunkan-pengajian', [
                'dari_kelas_id' => $kelas2->id,
                'ke_kelas_id' => $sma->id,
                'santri_ids' => [$s->id],
            ])
            ->assertSessionHas('error');

        $s->refresh();
        $this->assertSame('Kelas 2', $s->kelas_pengajian);
        $this->assertSame('2 SMA', $s->kelas_sekolah);
    }

    public function test_turunkan_pengajian_ditolak_bila_bukan_anggota_kelas_asal(): void
    {
        $kelas1 = $this->kelasPengajian('Kelas 1');
        $kelas2 = $this->kelasPengajian('Kelas 2');
        $kelas3 = $this->kelasPengajian('Kelas 3');

        $orangLain = $this->buatSantri('Santri Kelas Tiga', 'Kelas 3');

        $this->actingAs($this->admin())
            ->post('/kelas/turunkan-pengajian', [
                'dari_kelas_id' => $kelas2->id,
                'ke_kelas_id' => $kelas1->id,
                'santri_ids' => [$orangLain->id],
            ])
            ->assertSessionHas('error');

        $this->assertSame('Kelas 3', $orangLain->fresh()->kelas_pengajian);
        $this->assertSame(0, $kelas1->fresh()->jumlahSantri());
    }

    // ------------------------------------------------------------------
    // 5) Endpoint anggota untuk panel
    // ------------------------------------------------------------------

    public function test_endpoint_anggota_mengembalikan_roster_kelas_pengajian(): void
    {
        $takh1 = $this->kelasPengajian('Takhosus I');
        $this->kelasPengajian('Takhosus II');

        $this->buatSantri('Budi Santoso', 'Takhosus I', '2 SMA');
        $this->buatSantri('Ani Lestari', 'Takhosus I');

        $d = $this->actingAs($this->admin())
            ->getJson('/kelas/' . $takh1->id . '/anggota')
            ->assertOk()
            ->json();

        $this->assertSame('Takhosus I', $d['kelas']);
        $this->assertSame(KelasMaster::PENGAJIAN, $d['jenis']);
        $this->assertSame(20, $d['kapasitas']);
        $this->assertCount(2, $d['anggota']);

        // Nama sudah urut, dan kelas sekolah ikut terbawa supaya guru bisa
        // melihat konteks orangnya.
        $this->assertSame(['Ani Lestari', 'Budi Santoso'], array_column($d['anggota'], 'nama'));
        $sewa = collect($d['anggota'])->firstWhere('nama', 'Budi Santoso');
        $this->assertSame('2 SMA', $sewa['kelas_sekolah']);
    }

    public function test_endpoint_anggota_menolak_kelas_sekolah(): void
    {
        $sma = $this->kelasSekolah('2 SMA');

        $d = $this->actingAs($this->admin())
            ->getJson('/kelas/' . $sma->id . '/anggota')
            ->assertOk()
            ->json();

        $this->assertSame([], $d['anggota']);
        $this->assertStringContainsString('naik otomatis', $d['pesan']);
    }
}