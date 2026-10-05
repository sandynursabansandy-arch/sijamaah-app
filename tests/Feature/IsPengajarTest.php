<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsPengajarTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::factory()->create(['name' => 'Admin Test', 'role' => 'admin']);
    }

    protected function makeKamar(): Kamar
    {
        return Kamar::create(['nama_kamar' => 'Kamar A']);
    }

    /** Base POST payload for santri creation; callers override jabatan/is_pengajar. */
    protected function basePayload(string $nama): array
    {
        return [
            'nama' => $nama,
            'kamar' => 'Kamar A',
            'kelas' => 'Kelas',
            'no_hp_wali' => '081234567890',
            'foto_rumah_consent' => '1',
            'no_whatsapp' => '6281234567890',
        ];
    }

    /** is_pengajar NOT auto-set for Mudir jabatan without checkbox. */
    public function test_santri_with_mudir_jabatan_is_pengajar_true(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Mudir') + ['jabatan' => 'Mudir'];
        $this->post('/santri', $payload)->assertRedirect(route('santri.create'));

        $santri = Santri::where('nama', 'Santri Mudir')->first();
        $this->assertNotNull($santri);
        $this->assertFalse((bool) $santri->is_pengajar, 'is_pengajar harus false jika checkbox tidak dicentang meski jabatan Mudir');
    }

    /** Mudir with checkbox checked → is_pengajar = true. */
    public function test_santri_with_mudir_jabatan_and_checkbox_is_pengajar_true(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Mudir Checked') + ['jabatan' => 'Mudir', 'is_pengajar' => '1'];
        $this->post('/santri', $payload)->assertRedirect(route('santri.create'));

        $santri = Santri::where('nama', 'Santri Mudir Checked')->first();
        $this->assertNotNull($santri);
        $this->assertTrue((bool) $santri->is_pengajar, 'Checkbox is_pengajar harus override jabatan non-pengajar');
    }

    /** Auto-set is_pengajar = true for Mustahik. */
    public function test_santri_with_mustahik_jabatan_is_pengajar_false_without_checkbox(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Mustahik') + ['jabatan' => 'Mustahik'];
        $this->post('/santri', $payload)->assertRedirect(route('santri.create'));

        $santri = Santri::where('nama', 'Santri Mustahik')->first();
        $this->assertNotNull($santri);
        $this->assertFalse((bool) $santri->is_pengajar, 'is_pengajar harus false jika checkbox tidak dicentang meski jabatan Mustahik');
    }

    /** Auto-set is_pengajar = true for Munawwib. */
    public function test_santri_with_munawwib_jabatan_is_pengajar_false_without_checkbox(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Munawwib') + ['jabatan' => 'Munawwib'];
        $this->post('/santri', $payload)->assertRedirect(route('santri.create'));

        $santri = Santri::where('nama', 'Santri Munawwib')->first();
        $this->assertNotNull($santri);
        $this->assertFalse((bool) $santri->is_pengajar, 'is_pengajar harus false jika checkbox tidak dicentang meski jabatan Munawwib');
    }

    /** Non-pengajar jabatan (e.g. Wali) should set is_pengajar = false. */
    public function test_santri_with_non_pengajar_jabatan_is_pengajar_false(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Wali') + ['jabatan' => 'Wali'];
        $this->post('/santri', $payload)->assertRedirect(route('santri.create'));

        $santri = Santri::where('nama', 'Santri Wali')->first();
        $this->assertNotNull($santri);
        $this->assertFalse((bool) $santri->is_pengajar, 'is_pengajar harus false untuk jabatan non-pengajar');
    }

    /** No jabatan, no checkbox → is_pengajar = false. */
    public function test_santri_without_jabatan_is_pengajar_false(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Biasa');
        $this->post('/santri', $payload)->assertRedirect(route('santri.create'));

        $santri = Santri::where('nama', 'Santri Biasa')->first();
        $this->assertNotNull($santri);
        $this->assertFalse((bool) $santri->is_pengajar);
    }

    /** Checkbox override: is_pengajar true even without Pengajar jabatan. */
    public function test_santri_with_checkbox_override_is_pengajar_true(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Manual') + [
            'jabatan' => 'Wali',
            'is_pengajar' => '1',
        ];
        $this->post('/santri', $payload)->assertRedirect(route('santri.create'));

        $santri = Santri::where('nama', 'Santri Manual')->first();
        $this->assertNotNull($santri);
        $this->assertTrue((bool) $santri->is_pengajar, 'Checkbox is_pengajar harus override jabatan non-pengajar');
    }

    /** Dashboard should only display santri with is_pengajar = true. */
    public function test_dashboard_only_shows_pengajar_santri(): void
    {
        $kamar = $this->makeKamar();
        $pengajar = Santri::create([
            'nis' => '001', 'nama' => 'Pengajar Satu', 'kamar_id' => $kamar->id,
            'kelas' => 'K', 'status' => 'Aktif', 'is_pengajar' => true,
        ]);
        $bukanPengajar = Santri::create([
            'nis' => '002', 'nama' => 'Bukan Pengajar', 'kamar_id' => $kamar->id,
            'kelas' => 'K', 'status' => 'Aktif', 'is_pengajar' => false,
        ]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Pengajar Satu', $html, 'Pengajar harus muncul di dashboard');
        $this->assertStringNotContainsString('Bukan Pengajar', $html, 'Non-pengajar tidak boleh muncul di dashboard');
    }

    /** Input cepat page should only display pengajar santri in attendence form. */
    public function test_input_cepat_only_shows_pengajar_santri(): void
    {
        $kamar = $this->makeKamar();
        Santri::create([
            'nis' => '001', 'nama' => 'Pengajar Input', 'kamar_id' => $kamar->id,
            'kelas' => 'K', 'status' => 'Aktif', 'is_pengajar' => true,
        ]);
        Santri::create([
            'nis' => '002', 'nama' => 'Bukan Pengajar Input', 'kamar_id' => $kamar->id,
            'kelas' => 'K', 'status' => 'Aktif', 'is_pengajar' => false,
        ]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $html = $this->get('/input-cepat')->assertOk()->getContent();

        $this->assertStringContainsString('Pengajar Input', $html);
        $this->assertStringNotContainsString('Bukan Pengajar Input', $html);
    }

    /** Update santri: toggle is_pengajar via checkbox. */
    public function test_update_santri_can_toggle_is_pengajar(): void
    {
        $kamar = $this->makeKamar();
        $santri = Santri::create([
            'nis' => '001', 'nama' => 'Santri Test', 'kamar_id' => $kamar->id,
            'kelas' => 'K', 'status' => 'Aktif', 'is_pengajar' => false,
        ]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Test') + [
            'jabatan' => 'Wali',
            'is_pengajar' => '1',
            '_method' => 'PATCH',
        ];
        $this->call('PATCH', "/santri/{$santri->id}", $payload)->assertRedirect(route('santri.edit', $santri->id));

        $santri->refresh();
        $this->assertTrue((bool) $santri->is_pengajar, 'is_pengajar harus true setelah update dengan checkbox');
    }

    /** Update santri: uncheck is_pengajar and non-Pengajar jabatan → false. */
    public function test_update_santri_can_set_is_pengajar_false(): void
    {
        $kamar = $this->makeKamar();
        $santri = Santri::create([
            'nis' => '001', 'nama' => 'Santri Test', 'kamar_id' => $kamar->id,
            'kelas' => 'K', 'status' => 'Aktif', 'is_pengajar' => true, 'jabatan' => 'Mudir',
        ]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $payload = $this->basePayload('Santri Test') + [
            'jabatan' => 'Wali',
            '_method' => 'PATCH',
        ];
        $this->call('PATCH', "/santri/{$santri->id}", $payload)->assertRedirect(route('santri.edit', $santri->id));

        $santri->refresh();
        $this->assertFalse((bool) $santri->is_pengajar, 'is_pengajar harus false setelah update ke jabatan non-pengajar');
    }

    /** Create form checkbox should be pre-checked for existing Pengajar jabatan. */
    public function test_create_form_checkbox_prefers_jabatan_logic(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $html = $this->get('/santri/create')->assertOk()->getContent();
        $this->assertStringContainsString('is_pengajar', $html);
        $this->assertStringContainsString('Ikut presensi (pengajar/mudir/mustahik/munawwib)', $html);
    }
}
