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
 * Master kelas + wizard Naik Kelas + Buka Tahun Ajar Baru.
 *
 * Skenario inti: naik kelas dilakukan per KELAS, bukan per Santri, jadi
 * 1.500 Santri di 7 kelas cukup 7 klik per tahun (bukan 1.500 input manual).
 */
class KelasMasterTest extends TestCase
{
    use RefreshDatabase;

    protected string $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tahun = TahunAjar::sekarang();
        $this->buatDataDasar();
    }

    protected function admin(): User
    {
        return User::factory()->create(['name' => 'Admin Uji', 'role' => 'admin']);
    }

    protected function kelas(string $nama, ?string $tahun = null): KelasMaster
    {
        $tahun = $tahun ?: $this->tahun;
        $p = TahunAjar::parseNamaKelas($nama);

        return KelasMaster::firstOrCreate(
            ['tahun_ajar' => $tahun, 'nama' => $nama],
            [
                'jenjang' => $p['jenjang'],
                'tingkat' => $p['tingkat'],
                'jenis' => TahunAjar::deteksiJenis($nama),
                'aktif' => true,
            ]
        );
    }

    protected function buatSantri(string $nama, string $kelas, ?string $tahun = null): Santri
    {
        $kamar = Kamar::firstOrCreate(['nama_kamar' => 'Kamar Uji']);

        return Santri::create([
            'nama' => $nama,
            'nis' => 'S' . crc32($nama),
            'kamar_id' => $kamar->id,
            'kelas' => $kelas,
            'kelas_master_id' => $this->kelas($kelas, $tahun)->id,
            'no_whatsapp' => '081234567890',
            'alamat_desa' => 'Sukamaju',
            'alamat_rt' => '01',
        ]);
    }

    /** Master kelas 5 jenjang + 7 Santri (2 di 7 SMP, 3 di 8 SMP). */
    protected function buatDataDasar(): void
    {
        foreach (['7 SMP', '8 SMP', '9 SMP', '10 SMA', 'Ibtida 1'] as $nama) {
            $this->kelas($nama);
        }

        $this->buatSantri('Ahmad', '7 SMP');
        $this->buatSantri('Budi', '7 SMP');
        $this->buatSantri('Citra', '8 SMP');
        $this->buatSantri('Dewi', '8 SMP');
        $this->buatSantri('Eko', '8 SMP');
        $this->buatSantri('Fajar', '9 SMP');
        $this->buatSantri('Gita', 'Ibtida 1');
    }

    // ------------------------------------------------------------------
    // Helper TahunAjar
    // ------------------------------------------------------------------

    public function test_parsing_nama_kelas_membaca_jenjang_dan_tingkat(): void
    {
        $p = TahunAjar::parseNamaKelas('7 SMP');
        $this->assertSame('SMP', $p['jenjang']);
        $this->assertSame(7, $p['tingkat']);

        $p = TahunAjar::parseNamaKelas('10 SMA');
        $this->assertSame('SMA', $p['jenjang']);
        $this->assertSame(10, $p['tingkat']);

        $p = TahunAjar::parseNamaKelas('Ibtida 1');
        $this->assertSame('Ibtida', $p['jenjang']);
        $this->assertSame(1, $p['tingkat']);

        $p = TahunAjar::parseNamaKelas('1A');
        $this->assertSame(1, $p['tingkat']);
        $this->assertSame('A', $p['sisa']);
    }

    public function test_nama_kelas_berikutnya_naik_satu_tingkat(): void
    {
        $this->assertSame('8 SMP', TahunAjar::namaKelasBerikutnya('7 SMP'));
        $this->assertSame('9 SMP', TahunAjar::namaKelasBerikutnya('8 SMP'));
        $this->assertSame('10 SMA', TahunAjar::namaKelasBerikutnya('9 SMP'));
        $this->assertSame('Ibtida 2', TahunAjar::namaKelasBerikutnya('Ibtida 1'));
        $this->assertSame('2A', TahunAjar::namaKelasBerikutnya('1A'));

        // Gaya penulisan asal dipertahankan: Romawi tetap Romawi, dan kelas
        // berjenjang-depan tidak dibalik jadi "2 Ibtida".
        $this->assertSame('XII IPA 1', TahunAjar::namaKelasBerikutnya('XI IPA 1'));
        $this->assertSame('XII', TahunAjar::namaKelasBerikutnya('XI'));
        $this->assertSame('Sanawiyah 3', TahunAjar::namaKelasBerikutnya('Sanawiyah 2'));
    }

    public function test_kelas_tingkat_tertinggi_tidak_punya_kelas_berikutnya(): void
    {
        // Lulus SMP/MTs pindah jenjang ke SMA, lalu naik seperti biasa
        // sampai kelas 12 yang memang tidak punya tingkat berikutnya.
        $this->assertSame('10 SMA', TahunAjar::namaKelasBerikutnya('9 SMP'));
        $this->assertSame('12 SMA', TahunAjar::namaKelasBerikutnya('11 SMA'));
        $this->assertNull(TahunAjar::namaKelasBerikutnya('12 SMA'));
    }

    public function test_perhitungan_tahun_ajar(): void
    {
        $this->assertSame('2027/2028', TahunAjar::berikutnya('2026/2027'));
        $this->assertSame('2025/2026', TahunAjar::sebelumnya('2026/2027'));
        $this->assertTrue(TahunAjar::valid('2026/2027'));
        $this->assertFalse(TahunAjar::valid('semester ini'));
    }

    // ------------------------------------------------------------------
    // Backfill migration
    // ------------------------------------------------------------------

    public function test_backfill_migration_menautkan_santri_berupa_kolom_kelas_teks(): void
    {
        $kamar = Kamar::firstOrCreate(['nama_kamar' => 'Kamar Uji']);
        Santri::create([
            'nama' => 'Santri Legacy',
            'nis' => 'SLEGACY',
            'kamar_id' => $kamar->id,
            'kelas' => '6 SMP',
            'kelas_master_id' => null,
        ]);

        $this->assertFalse(KelasMaster::where('nama', '6 SMP')->exists());

        // Jalankan ulang logika backfill (sama dengan yang dijalankan migration).
        $migration = require base_path('database/migrations/2026_10_05_000001_create_kelas_master_table.php');
        $migration->up();

        $master = KelasMaster::tahunAjar($this->tahun)->where('nama', '6 SMP')->first();
        $this->assertNotNull($master, 'Master kelas harus dibuat dari nilai kelas yang sudah ada.');
        $this->assertSame('SMP', $master->jenjang);
        $this->assertSame(6, $master->tingkat);

        $legacy = Santri::where('nis', 'SLEGACY')->first();
        $this->assertSame($master->id, $legacy->kelas_master_id);
    }

    // ------------------------------------------------------------------
    // CRUD master kelas
    // ------------------------------------------------------------------

    public function test_tambah_kelas_membaca_jenjang_otomatis(): void
    {
        $this->actingAs($this->admin())
            ->post('/kelas', ['nama' => '11 IPA', 'tahun_ajar' => $this->tahun])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kelas_master', [
            'nama' => '11 IPA',
            'tahun_ajar' => $this->tahun,
            'jenjang' => 'SMA',
            'tingkat' => 11,
        ]);
    }

    public function test_tambah_kelas_ditolak_kalau_duplikat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/kelas', ['nama' => '9 SMP', 'tahun_ajar' => $this->tahun]);
        $this->actingAs($admin)
            ->post('/kelas', ['nama' => '9 SMP', 'tahun_ajar' => $this->tahun])
            ->assertSessionHas('error');
    }

    public function test_tambah_kelas_ditolak_kalau_format_tahun_ajar_salah(): void
    {
        $this->actingAs($this->admin())
            ->post('/kelas', ['nama' => 'X IPA', 'tahun_ajar' => 'tahun depan'])
            ->assertSessionHas('error');
    }

    public function test_ubah_nama_kelas_menyesuaikan_kolom_kelas_pada_santri(): void
    {
        $master = $this->kelas('7 SMP');

        $this->actingAs($this->admin())
            ->put('/kelas/' . $master->id, ['nama' => '7 SMP A'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kelas_master', ['id' => $master->id, 'nama' => '7 SMP A']);
        $this->assertDatabaseHas('santris', ['kelas_master_id' => $master->id, 'kelas' => '7 SMP A']);
    }

    public function test_hapus_kelas_ditolak_kalau_masih_punya_santri(): void
    {
        $master = $this->kelas('7 SMP');

        $this->actingAs($this->admin())
            ->delete('/kelas/' . $master->id)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kelas_master', ['id' => $master->id]);
    }

    public function test_hapus_kelas_kosong_berhasil(): void
    {
        $kosong = $this->kelas('12 SMA');

        $this->actingAs($this->admin())
            ->delete('/kelas/' . $kosong->id)
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kelas_master', ['id' => $kosong->id]);
    }

    // ------------------------------------------------------------------
    // Naik Kelas (satu klik per kelas)
    // ------------------------------------------------------------------

    public function test_naik_kelas_memindahkan_seluruh_santri_satu_kelas(): void
    {
        $dari = $this->kelas('7 SMP');
        $ke = $this->kelas('8 SMP');

        $this->actingAs($this->admin())
            ->post('/kelas/naik', ['dari_kelas_id' => $dari->id, 'ke_kelas_id' => $ke->id])
            ->assertSessionHas('success');

        $this->assertSame(0, $dari->fresh()->santris()->count());
        $this->assertSame(5, $ke->fresh()->santris()->count());
    }

    public function test_naik_kelas_tidak_menyentuh_nama_nis_kamar_alamat_whatsapp(): void
    {
        $dari = $this->kelas('7 SMP');
        $ke = $this->kelas('8 SMP');

        $kolom = ['id', 'nama', 'nis', 'kamar_id', 'alamat_desa', 'alamat_rt', 'no_whatsapp'];
        $ids = Santri::where('kelas_master_id', $dari->id)->pluck('id');
        $sebelum = Santri::whereIn('id', $ids)->get()
            ->map(fn($s) => $s->only($kolom))->sortBy('id')->values()->all();

        $this->actingAs($this->admin())->post('/kelas/naik', [
            'dari_kelas_id' => $dari->id,
            'ke_kelas_id' => $ke->id,
        ])->assertSessionHas('success');

        $sesudah = Santri::whereIn('id', $ids)->get()
            ->map(fn($s) => $s->only($kolom))->sortBy('id')->values()->all();

        $this->assertSame($sebelum, $sesudah);
    }

    public function test_naik_kelas_memperbarui_kolom_kelas_teks(): void
    {
        $dari = $this->kelas('7 SMP');
        $ke = $this->kelas('8 SMP');

        $this->actingAs($this->admin())->post('/kelas/naik', [
            'dari_kelas_id' => $dari->id,
            'ke_kelas_id' => $ke->id,
        ]);

        $this->assertSame(0, Santri::where('kelas', '7 SMP')->count());
        $this->assertSame(5, Santri::where('kelas', '8 SMP')->count());
    }

    public function test_naik_kelas_ditolak_kalau_asal_dan_tujuan_sama(): void
    {
        $dari = $this->kelas('7 SMP');

        $this->actingAs($this->admin())
            ->post('/kelas/naik', ['dari_kelas_id' => $dari->id, 'ke_kelas_id' => $dari->id])
            ->assertSessionHas('error');

        $this->assertSame(2, $dari->fresh()->santris()->count());
    }

    public function test_naik_kelas_mengikuti_santri_yang_belum_punya_master(): void
    {
        $kamar = Kamar::firstOrCreate(['nama_kamar' => 'Kamar Uji']);
        $lama = Santri::create([
            'nama' => 'Santri Legacy',
            'nis' => 'SLEGACY2',
            'kamar_id' => $kamar->id,
            'kelas' => '7 SMP',
            'kelas_master_id' => null,
        ]);

        $dari = $this->kelas('7 SMP');
        $ke = $this->kelas('8 SMP');

        $this->actingAs($this->admin())->post('/kelas/naik', [
            'dari_kelas_id' => $dari->id,
            'ke_kelas_id' => $ke->id,
        ])->assertSessionHas('success');

        $lama->refresh();
        $this->assertSame($ke->id, $lama->kelas_master_id);
        $this->assertSame('8 SMP', $lama->kelas);
    }

    public function test_pratinjau_naik_kelas_menghitung_jumlah_santri(): void
    {
        $dari = $this->kelas('7 SMP');

        $this->actingAs($this->admin())
            ->getJson('/kelas/' . $dari->id . '/pratinjau')
            ->assertOk()
            ->assertJson([
                'kelas' => '7 SMP',
                'tahun_ajar' => $this->tahun,
                'jumlah' => 2,
                'berikutnya' => '8 SMP',
            ]);
    }

    // ------------------------------------------------------------------
    // Buka Tahun Ajar Baru
    // ------------------------------------------------------------------

    public function test_buka_tahun_ajar_menyalin_kelas_dengan_tingkat_naik(): void
    {
        $this->actingAs($this->admin())
            ->post('/kelas/buka-tahun-ajar', [
                'dari_tahun_ajar' => $this->tahun,
                'ke_tahun_ajar' => '2027/2028',
            ])
            ->assertSessionHas('success');

        $baru = KelasMaster::tahunAjar('2027/2028')->pluck('nama')->all();
        $this->assertContains('8 SMP', $baru);
        $this->assertContains('9 SMP', $baru);
        $this->assertContains('10 SMA', $baru);
        $this->assertContains('Ibtida 2', $baru);

        $tingkat = KelasMaster::tahunAjar('2027/2028')->where('nama', '8 SMP')->first();
        $this->assertSame(8, $tingkat->tingkat);
    }

    public function test_buka_tahun_ajar_memindahkan_semua_santri(): void
    {
        $this->actingAs($this->admin())
            ->post('/kelas/buka-tahun-ajar', [
                'dari_tahun_ajar' => $this->tahun,
                'ke_tahun_ajar' => '2027/2028',
                'pindahkan_santri' => '1',
            ])
            ->assertSessionHas('success');

        $this->assertSame(0, Santri::where('kelas', '7 SMP')->count());
        $this->assertSame(0, Santri::where('kelas', 'Ibtida 1')->count());

        $semua = Santri::all();
        $this->assertCount(7, $semua);
        foreach ($semua as $s) {
            $this->assertNotNull($s->kelas_master_id, 'Semua Santri harus punya master kelas.');
            $this->assertSame('2027/2028', $s->kelasMaster->tahun_ajar);
        }

        // Diproses dari tingkat tertinggi: 9 SMP (1) → 10 SMA, 8 SMP (3) → 9 SMP,
        // 7 SMP (2) → 8 SMP. Tidak ada Santri yang naik dua tingkat sekaligus.
        $this->assertSame(2, Santri::where('kelas', '8 SMP')->count());
        $this->assertSame(1, Santri::where('kelas', 'Ibtida 2')->count());
    }

    public function test_buka_tahun_ajar_tanpa_pindah_tidak_mengubah_santri(): void
    {
        $this->actingAs($this->admin())
            ->post('/kelas/buka-tahun-ajar', [
                'dari_tahun_ajar' => $this->tahun,
                'ke_tahun_ajar' => '2027/2028',
            ])
            ->assertSessionHas('success');

        $this->assertSame(2, Santri::where('kelas', '7 SMP')->count());
        $this->assertNotEmpty(KelasMaster::tahunAjar('2027/2028')->get());
    }

    public function test_buka_tahun_ajar_menandai_tahun_lama_tidak_aktif(): void
    {
        $this->actingAs($this->admin())->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => '2027/2028',
        ])->assertSessionHas('success');

        $this->assertSame(0, KelasMaster::tahunAjar($this->tahun)->where('aktif', true)->count());
        $this->assertGreaterThan(0, KelasMaster::tahunAjar('2027/2028')->where('aktif', true)->count());
    }

    public function test_buka_tahun_ajar_ditolak_kalau_tahun_tujuan_sudah_ada(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => '2027/2028',
        ])->assertSessionHas('success');

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => '2027/2028',
        ])->assertSessionHas('error');
    }

    public function test_buka_tahun_ajar_ditolak_kalau_tahun_awal_tidak_valid(): void
    {
        $this->actingAs($this->admin())->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => 'entah',
            'ke_tahun_ajar' => '2027/2028',
        ])->assertSessionHas('error');
    }

    public function test_kelas_tingkat_tertinggi_disalin_dengan_nama_sama(): void
    {
        $this->kelas('12 SMA');

        $this->actingAs($this->admin())->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => '2027/2028',
        ])->assertSessionHas('success');

        $salin = KelasMaster::tahunAjar('2027/2028')->where('nama', '12 SMA')->first();
        $this->assertNotNull($salin, 'Kelas 12 SMA harus tetap ada di tahun baru (perlu disunting manual).');
    }

    // ------------------------------------------------------------------
    // Akses & halaman
    // ------------------------------------------------------------------

    public function test_halaman_kelas_hanya_bisa_diakses_admin_dan_musyrif(): void
    {
        $pengasuh = User::factory()->create(['name' => 'Pengasuh', 'role' => 'pengasuh']);

        $this->actingAs($pengasuh)->get('/kelas')->assertForbidden();
    }

    public function test_halaman_kelas_tampil_untuk_admin_dan_musyrif(): void
    {
        foreach (['admin', 'musyrif'] as $role) {
            $user = User::factory()->create(['name' => $role, 'role' => $role]);

            $this->actingAs($user)
                ->get('/kelas')
                ->assertOk()
                ->assertSee('Naik Kelas')
                ->assertSee('Buka Tahun Ajar Baru');
        }
    }

    public function test_halaman_kelas_menampilkan_saran_kelas_berikutnya(): void
    {
        $this->actingAs($this->admin())->get('/kelas')->assertOk()->assertSee('8 SMP');
    }

    public function test_filter_kelas_di_halaman_santri(): void
    {
        $master = $this->kelas('8 SMP');

        $response = $this->actingAs($this->admin())
            ->get('/santri?tahun_ajar=' . $this->tahun . '&kelas_id=' . $master->id);

        $response->assertOk();
        $this->assertSame(3, $response->viewData('santris')->total());
        // Dropdown tetap menampilkan semua kelas di tahun ajar aktif supaya bisa diganti.
        $this->assertCount(5, $response->viewData('daftarKelas'));
        $this->assertSame($this->tahun, $response->viewData('tahunAjar'));
        $this->assertSame($master->id, (int) $response->viewData('kelasId'));
    }

    public function test_halaman_santri_tampil_tanpa_filter_kelas(): void
    {
        $response = $this->actingAs($this->admin())->get('/santri');

        $response->assertOk();
        $this->assertCount(5, $response->viewData('daftarKelas'));
    }

    public function test_simpan_santri_otomatis_mendaftarkan_kelas_baru(): void
    {
        $this->actingAs($this->admin())->post('/santri', [
            'nama' => 'Santri Baru',
            'kamar' => 'Kamar Uji',
            'kelas' => '11 IPA',
        ])->assertSessionHas('success');

        $santri = Santri::where('nama', 'Santri Baru')->first();
        $this->assertNotNull($santri->kelas_master_id);
        $this->assertSame('11 IPA', $santri->kelasMaster->nama);
        $this->assertSame('SMA', $santri->kelasMaster->jenjang);
        $this->assertSame(11, $santri->kelasMaster->tingkat);
        $this->assertSame('IPA', $santri->kelasMaster->sisa);
    }

    // ------------------------------------------------------------------
    // Skenario 1 Santri (Sany): 2023 -> 2027, tanpa input manual per Santri
    // ------------------------------------------------------------------

    public function test_skenario_sany_naik_kelas_setiap_tahun(): void
    {
        $admin = $this->admin();
        $tahunMulai = TahunAjar::sekarang();

        $sany = $this->buatSantri('Sany', '7 SMP', $tahunMulai);
        $rencana = [
            ['7 SMP', '8 SMP'],
            ['8 SMP', '9 SMP'],
            ['9 SMP', '10 SMA'],
            ['10 SMA', '11 SMA'],
        ];

        foreach ($rencana as [$dariNama, $keNama]) {
            $dari = $this->kelas($dariNama);
            $ke = $this->kelas($keNama);

            if ($sany->fresh()->kelas_master_id !== $dari->id) {
                continue;
            }

            $this->actingAs($admin)->post('/kelas/naik', [
                'dari_kelas_id' => $dari->id,
                'ke_kelas_id' => $ke->id,
            ])->assertSessionHas('success');
        }

        $sany->refresh();
        $this->assertSame('11 SMA', $sany->kelas);
        $this->assertSame('11 SMA', $sany->kelasMaster->nama);
    }

    public function test_buka_tahun_ajar_otomatis_menaikkan_tingkat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => '2027/2028',
            'pindahkan_santri' => '1',
        ])->assertSessionHas('success');

        $peta = [
            '8 SMP' => 2,   // 7 SMP naik ke 8 SMP
            '9 SMP' => 3,   // 8 SMP (3 + 2) naik ke 9 SMP
            '10 SMA' => 1,  // 9 SMP naik ke 10 SMA
            'Ibtida 2' => 1,
        ];

        foreach ($peta as $nama => $jumlah) {
            $this->assertSame($jumlah, Santri::where('kelas', $nama)->count(), 'Kelas ' . $nama);
        }
    }

    public function test_tujuh_kelas_cukup_tujuh_klik(): void
    {
        // Skenario 1.500 Santri di 7 kelas: cukup 7 operasi, bukan 1.500.
        $admin = $this->admin();
        $daftarKelas = ['7 SMP', '8 SMP', '9 SMP', '10 SMA', '11 SMA', '12 SMA', 'Ibtida 1'];

        foreach ($daftarKelas as $nama) {
            $this->kelas($nama);
            Santri::insert(array_map(fn($i) => [
                'nama' => "Santri $nama $i",
                'nis' => md5($nama . $i),
                'kamar_id' => Kamar::firstOrCreate(['nama_kamar' => 'Kamar Uji'])->id,
                'kelas' => $nama,
                'kelas_master_id' => $this->kelas($nama)->id,
            ], range(1, 5)));
        }

        $totalSebelum = Santri::count();
        $this->assertGreaterThanOrEqual(35, $totalSebelum);

        $operations = 0;
        foreach (['7 SMP' => '8 SMP', '8 SMP' => '9 SMP', '9 SMP' => '10 SMA'] as $dariNama => $keNama) {
            $this->actingAs($admin)->post('/kelas/naik', [
                'dari_kelas_id' => $this->kelas($dariNama)->id,
                'ke_kelas_id' => $this->kelas($keNama)->id,
            ])->assertSessionHas('success');
            $operations++;
        }

        $this->assertSame(3, $operations, 'Tiga operasi saja, bukan satu per Santri.');
        $this->assertSame($totalSebelum, Santri::count(), 'Jumlah Santri tidak boleh berubah.');
        $this->assertSame(0, Santri::where('kelas', '7 SMP')->count());
    }

    // ------------------------------------------------------------------
    // Kelas pengajian: TIDAK naik kelas tiap tahun
    // ------------------------------------------------------------------

    public function test_tebakan_jenis_kelas_pengajian_dan_formal()
    {
        // Kelas pengajian yang dipakai pesantren: angka polos & takhosus.
        foreach (['1', '2', '3', '4', '5', '6', 'Takhosus I', 'Takhosus II'] as $nama) {
            $this->assertSame(
                TahunAjar::JENIS_PENGAJIAN,
                TahunAjar::deteksiJenis($nama),
                '"' . $nama . '" seharusnya terdeteksi sebagai pengajian.'
            );
        }

        // Kelas sekolah naik tingkat, jadi harus formal.
        foreach (['1 SMP', '7 SMP', '9 SMP', 'Ibtida 1', 'Sanawiyah 2', '10 SMA', 'XI IPA 1', '1A'] as $nama) {
            $this->assertSame(
                TahunAjar::JENIS_FORMAL,
                TahunAjar::deteksiJenis($nama),
                '"' . $nama . '" seharusnya terdeteksi sebagai formal.'
            );
        }
    }

    public function test_kelas_pengajian_tetap_saat_buka_tahun_ajar_baru()
    {
        $admin = $this->admin();

        foreach (['1', '2', '3', '4', '5', '6', 'Takhosus I', 'Takhosus II'] as $nama) {
            $this->buatSantri('Santri ' . $nama, $nama);
        }

        $tahunBaru = TahunAjar::berikutnya($this->tahun);

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => $tahunBaru,
            'pindahkan_santri' => 1,
        ])->assertSessionHas('success');

        // Semua kelas pengajian harus ada di tahun baru dengan NAMA SAMA.
        foreach (['1', '2', '3', '4', '5', '6', 'Takhosus I', 'Takhosus II'] as $nama) {
            $baru = KelasMaster::tahunAjar($tahunBaru)->where('nama', $nama)->first();

            $this->assertNotNull($baru, 'Kelas "' . $nama . '" harus ikut tersalin ke ' . $tahunBaru . '.');
            $this->assertSame(
                KelasMaster::PENGAJIAN,
                $baru->jenis,
                'Kelas "' . $nama . '" harus tetap pengajian di tahun baru.'
            );
        }

        // Master kelas pengajian ikut tersalin dengan nama sama dan Santri TETAP di
        // kelas itu — tidak naik ke kelas lain. Yang berubah hanya baris master
        // yang ditunjuk: dari kelas tahun lama ke kelas nama-sama di tahun baru,
        // supaya roster tahun aktif benar dan kelas tahun lama tidak lagi
        // menghitung orang yang sudah pindah tahun.
        foreach (['1', '2', '3', '4', '5', '6', 'Takhosus I', 'Takhosus II'] as $nama) {
            $santri = Santri::where('nama', 'Santri ' . $nama)->first();

            $this->assertNotNull($santri, 'Santri "' . $nama . '" harus ada.');
            $this->assertSame(
                $nama,
                $santri->kelas_pengajian,
                'Santri "' . $nama . '" harus tetap di kelas pengajian yang sama, bukan naik kelas.'
            );
            $this->assertSame(
                KelasMaster::tahunAjar($tahunBaru)->where('nama', $nama)->first()->id,
                $santri->kelas_pengajian_id,
                'Santri "' . $nama . '" harus tertaut ke kelas pengajian di tahun ajar ' . $tahunBaru . '.'
            );
            $this->assertSame(
                0,
                Santri::where('kelas_pengajian_id', $this->kelas($nama)->id)->count(),
                'Kelas pengajian lama "' . $nama . '" harus kehilangan anggotanya.'
            );
        }

        // Pesannya harus menyebut jumlah yang tertinggal, supaya admin tahu
        // ada yang perlu dipindah manual.
        $pesan = (string) session('success');
        $this->assertStringContainsString('TETAP di kelasnya yang sama', $pesan);
        $this->assertStringContainsString('takhosus', mb_strtolower($pesan));
    }

    public function test_santri_pengajian_bisa_dipindah_manual_lewat_wizard_naik()
    {
        $admin = $this->admin();
        $santri = $this->buatSantri('Santri Naik', '1');
        $asal = $this->kelas('1');
        $tujuan = $this->kelas('Takhosus I');

        $this->actingAs($admin)->post('/kelas/naik', [
            'dari_kelas_id' => $asal->id,
            'ke_kelas_id' => $tujuan->id,
        ])->assertSessionHas('success');

        $santri->refresh();
        $this->assertSame('Takhosus I', $santri->kelas_pengajian);
        $this->assertSame($tujuan->id, $santri->kelas_pengajian_id);
    }

    public function test_naik_kelas_ditolak_lintas_jenis_pengajian_dan_sekolah()
    {
        $admin = $this->admin();
        $pengajian = $this->kelas('Takhosus I');
        $sekolah = $this->kelas('7 SMP');

        $this->actingAs($admin)->post('/kelas/naik', [
            'dari_kelas_id' => $pengajian->id,
            'ke_kelas_id' => $sekolah->id,
        ])->assertSessionHas('error');

        $this->assertSame(
            2,
            Santri::where('kelas', '7 SMP')->count(),
            'Tidak ada Santri yang boleh berpindah karena jenisnya beda.'
        );
    }

    public function test_kelas_pengajian_tidak_naik_saat_buka_tahun_ajar_berulang()
    {
        $admin = $this->admin();
        $this->buatSantri('Santri Kelas 1', '1');

        $tahun = $this->tahun;

        // Tiga tahun berturut-turut: kelas pengajian tidak boleh bergeser dan
        // tidak boleh ikut naik bersama.
        for ($i = 0; $i < 3; $i++) {
            $berikut = TahunAjar::berikutnya($tahun);

            $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
                'dari_tahun_ajar' => $tahun,
                'ke_tahun_ajar' => $berikut,
                'pindahkan_santri' => 1,
            ])->assertSessionHas('success');

            $this->assertSame(
                0,
                Santri::where('kelas', '1')
                    ->whereHas('kelasMaster', fn ($q) => $q->where('tahun_ajar', $berikut))
                    ->count(),
                'Tahun ' . $berikut . ': Santri pengajian tidak boleh ikut naik sendiri.'
            );

            $tahun = $berikut;
        }

        // Tidak boleh muncul kelas "2" hasil kenaikan otomatis.
        $this->assertSame(0, KelasMaster::tahunAjar($tahun)->where('nama', '2')->count());
    }

    public function test_kelas_formal_tetap_naik_kelas_saat_buka_tahun_ajar()
    {
        $admin = $this->admin();
        $santri = $this->buatSantri('Zainuddin', '7 SMP');

        $tahunBaru = TahunAjar::berikutnya($this->tahun);

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => $tahunBaru,
            'pindahkan_santri' => 1,
        ])->assertSessionHas('success');

        $baru = KelasMaster::tahunAjar($tahunBaru)->where('nama', '8 SMP')->first();
        $this->assertNotNull($baru, 'Kelas formal harus naik ke "8 SMP".');
        $this->assertSame(KelasMaster::FORMAL, $baru->jenis);
        $this->assertSame('8 SMP', $santri->fresh()->kelas);
        $this->assertSame($baru->id, $santri->fresh()->kelas_master_id);
    }

    public function test_toggle_jenis_kelas_mengubah_perilaku_naik_kelas()
    {
        $admin = $this->admin();
        $santri = $this->buatSantri('Santri Kelas 1', '1');
        $kelas = KelasMaster::tahunAjar($this->tahun)->where('nama', '1')->first();

        $this->assertTrue($kelas->isPengajian());

        // Admin mengubahnya jadi formal → harus ikut naik.
        $this->actingAs($admin)
            ->patch('/kelas/' . $kelas->id . '/jenis', ['jenis' => 'formal'])
            ->assertSessionHas('success');

        $tahunBaru = TahunAjar::berikutnya($this->tahun);

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => $tahunBaru,
            'pindahkan_santri' => 1,
        ])->assertSessionHas('success');

        $this->assertNotNull(
            KelasMaster::tahunAjar($tahunBaru)->where('nama', '2')->first(),
            'Setelah diubah jadi formal, kelas "1" harus naik ke "2".'
        );
        $this->assertSame(0, Santri::where('kelas', '1')->count());
    }

    public function test_toggle_jenis_menolak_nilai_ngawur()
    {
        $admin = $this->admin();
        $this->buatSantri('Santri Kelas 1', '1');
        $kelas = KelasMaster::tahunAjar($this->tahun)->where('nama', '1')->first();

        $this->actingAs($admin)
            ->patch('/kelas/' . $kelas->id . '/jenis', ['jenis' => 'ngawur'])
            ->assertSessionHas('error');

        $this->assertSame(
            KelasMaster::PENGAJIAN,
            KelasMaster::find($kelas->id)->jenis,
            'Jenis kelas tidak boleh berubah karena input ngawur.'
        );
    }

    public function test_tambah_kelas_bisa_memilih_jenis_secara_manual()
    {
        $admin = $this->admin();

        // Nama berniat "formal", tapi admin memilih pengajian secara manual.
        $this->actingAs($admin)->post('/kelas', [
            'nama' => 'Kelas 1',
            'tahun_ajar' => $this->tahun,
            'jenis' => 'pengajian',
        ])->assertSessionHas('success');

        $this->assertSame(
            KelasMaster::PENGAJIAN,
            KelasMaster::tahunAjar($this->tahun)->where('nama', 'Kelas 1')->first()->jenis
        );
    }

    public function test_buka_tahun_ajar_ditolak_kalau_nama_tujuan_bentrok_dengan_pengajian()
    {
        $admin = $this->admin();

        // "1" sengaja dibuat formal supaya akan naik ke "2", yang dipakai pengajian.
        $kelasSatu = $this->kelas('1');
        $kelasSatu->update(['jenis' => KelasMaster::FORMAL]);
        $this->kelas('2'); // pengajian
        $this->buatSantri('Santri Kelas 1', '1');

        $this->actingAs($admin)->post('/kelas/buka-tahun-ajar', [
            'dari_tahun_ajar' => $this->tahun,
            'ke_tahun_ajar' => TahunAjar::berikutnya($this->tahun),
            'pindahkan_santri' => 1,
        ])->assertSessionHas('error');

        $this->assertSame(
            0,
            KelasMaster::tahunAjar(TahunAjar::berikutnya($this->tahun))->count(),
            'Tidak boleh ada kelas yang dibuat kalau tujuan bentrok.'
        );
    }

    public function test_halaman_kelas_menampilkan_badge_jenis()
    {
        $admin = $this->admin();
        $this->buatSantri('Santri Kelas 1', '1');
        $this->buatSantri('Santri Kelas 7', '7 SMP');

        $html = $this->actingAs($admin)->get('/kelas')->assertOk()->getContent();

        // Kelas split dua jalur, jadi halaman harus punya dua tabel terpisah.
        $this->assertStringContainsString('Kelas Pengajian', $html, 'Tabel kelas pengajian harus ada.');
        $this->assertStringContainsString('Kelas Sekolah', $html, 'Tabel kelas sekolah harus ada.');
        $this->assertStringContainsString('tidak naik otomatis', $html, 'Kelas pengajian harus dijelaskan tidak naik otomatis.');
        // route() hanya menghasilkan URL, bukan nama route.
        $this->assertStringContainsString('name="jenis" value="pengajian"', $html, 'Tombol pengajian harus ada.');
        $this->assertStringContainsString('name="jenis" value="formal"', $html, 'Tombol sekolah harus ada.');
        $this->assertStringContainsString('/jenis', $html, 'Form toggle harus menunjuk ke route jenis.');
        $this->assertStringContainsString('8 SMP', $html, 'Kelas sekolah masih menampilkan saran naik kelas.');
        $this->assertStringContainsString('Kelas Berikutnya', $html, 'Kolom saran naik kelas hanya ada di tabel sekolah.');
    }
}
