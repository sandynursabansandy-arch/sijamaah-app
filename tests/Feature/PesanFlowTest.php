<?php

namespace Tests\Feature;

use App\Models\AdminMessage;
use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesanFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function makeData(): array
    {
        $kamar = Kamar::create(['nama_kamar' => 'Kamar A']);
        $santri = Santri::create(['nis' => '001', 'nama' => 'Santri Pengasuh', 'kamar_id' => $kamar->id, 'kelas' => 'Kelas', 'status' => 'Aktif']);
        $admin = User::factory()->create(['name' => 'Admin Test', 'role' => 'admin']);
        $pengasuh = User::factory()->create(['name' => 'Santri Pengasuh', 'role' => 'pengasuh']);

        return compact('kamar', 'santri', 'admin', 'pengasuh');
    }

    public function test_zero_before_send(): void
    {
        ['pengasuh' => $pengasuh] = $this->makeData();
        $this->actingAs($pengasuh);

        $this->getJson('/pesan-user/jumlah')->assertOk()->assertJson(['count' => 0]);
        $this->getJson('/pesan-user/daftar')->assertOk()->assertJsonCount(0, 'items');
    }

    public function test_admin_send_read_and_destroy_flow(): void
    {
        ['santri' => $santri, 'admin' => $admin, 'pengasuh' => $pengasuh] = $this->makeData();

        // Admin kirim ke semua
        $this->actingAs($admin);
        $this->from('/pesan')->post('/pesan', ['pesan' => 'Pengumuman penting', 'mode' => 'all'])
            ->assertRedirect(route('pesan.index'));
        $this->assertDatabaseCount('admin_messages', 1);

        // Admin kirim per santri
        $this->from('/pesan')->post('/pesan', ['pesan' => 'Untuk kamu', 'mode' => 'santri', 'santri_id' => $santri->id])
            ->assertRedirect(route('pesan.index'));
        $this->assertDatabaseCount('admin_messages', 2);

        // User melihat badge & daftar
        $this->actingAs($pengasuh);
        $this->getJson('/pesan-user/jumlah')->assertOk()->assertJson(['count' => 2]);
        $this->getJson('/pesan-user/daftar')->assertOk()
            ->assertJsonCount(2, 'items')
            ->assertJsonPath('items.0.text', 'Untuk kamu')
            ->assertJsonPath('items.0.type', 'pesan');

        // Panel Saya menampilkan pesan
        $this->get('/panel-saya')->assertOk()->assertSee('Untuk kamu')->assertSee('Pesan dari Admin');

        // Tandai satu dibaca
        $first = AdminMessage::where('santri_id', $santri->id)->orderByDesc('created_at')->first();
        $this->post("/pesan-user/{$first->id}/baca")->assertOk();
        $this->getJson('/pesan-user/jumlah')->assertOk()->assertJson(['count' => 1]);

// Tandai semua dibaca
        $this->post('/pesan-user/baca-semua')->assertOk();
        $this->getJson('/pesan-user/jumlah')->assertOk()->assertJson(['count' => 0]);

        // Admin melihat NAMA santri yang membaca pesan
        $this->actingAs($admin);
        $this->get('/pesan')->assertOk()
            ->assertSee('Dibaca oleh 1 santri')
            ->assertSee('Santri Pengasuh');

        // User = pengasuh; pesan untuk pengasuh saja (bukan seluruh santri)
        $this->actingAs($pengasuh);
        $this->get('/panel-saya')->assertOk()->assertSee('Semua sudah dibaca');

// Admin hapus batch
        $batch = AdminMessage::where('santri_id', $santri->id)->first()->batch_id;
        $this->actingAs($admin);
        $this->delete("/pesan/{$batch}")->assertRedirect(route('pesan.index'));
        $this->assertDatabaseCount('admin_messages', 1);
    }

    public function test_user_can_delete_own_message_copy_and_mark_read(): void
    {
        ['santri' => $santri, 'admin' => $admin, 'pengasuh' => $pengasuh] = $this->makeData();

        $kamar2 = Kamar::create(['nama_kamar' => 'Kamar B']);
        $santri2 = Santri::create(['nis' => '009', 'nama' => 'Santri Lain', 'kamar_id' => $kamar2->id, 'kelas' => 'Kelas', 'status' => 'Aktif']);
        $pengasuh2 = User::factory()->create(['name' => 'Santri Lain', 'role' => 'pengasuh']);

        // Admin kirim batch ke 2 santri
        $this->actingAs($admin);
        $this->from('/pesan')->post('/pesan', ['pesan' => 'Pesan batch', 'mode' => 'all']);
        $this->assertDatabaseCount('admin_messages', 2);

        // Panel Saya user 1 menampilkan tombol Sudah dibaca + Hapus
        $this->actingAs($pengasuh);
        $this->get('/panel-saya')->assertOk()
            ->assertSee('Sudah dibaca')
            ->assertSee('Hapus');

        // User 1 hapus pesannya sendiri -> hanya salinannya yang hilang, punya user 2 utuh
        $mine = AdminMessage::where('santri_id', $santri->id)->first();
        $this->delete("/pesan-user/{$mine->id}")->assertOk();
        $this->assertDatabaseMissing('admin_messages', ['id' => $mine->id]);
        $this->assertDatabaseCount('admin_messages', 1);
        $this->assertDatabaseHas('admin_messages', ['santri_id' => $santri2->id]);

        // User 1 tidak bisa hapus pesan milik user 2
        $others = AdminMessage::where('santri_id', $santri2->id)->first();
        $this->delete("/pesan-user/{$others->id}")->assertOk();
        $this->assertDatabaseHas('admin_messages', ['id' => $others->id]);

        // Timpa ulang pesan untuk user 1, lalu tandai satu dibaca
        $this->actingAs($admin);
        $this->from('/pesan')->post('/pesan', ['pesan' => 'Pesan dua', 'mode' => 'all']);
        $this->actingAs($pengasuh);
        $unread = AdminMessage::where('santri_id', $santri->id)->where('is_read', false)->first();
        $this->post("/pesan-user/{$unread->id}/baca")->assertOk();
        $this->assertDatabaseHas('admin_messages', ['id' => $unread->id, 'is_read' => true]);

        // Admin melihat nama pembaca pada batch multi-penerima yang baru dibaca sebagian
        $this->actingAs($admin);
        $this->get('/pesan')->assertOk()->assertSee('Dibaca oleh 1 santri');
        $html = $this->get('/pesan')->getContent();
        $this->assertStringNotContainsString('Dibaca oleh 2 santri', $html);
    }

    public function test_kamar_mode_delivers_only_to_kamar_members(): void
    {
        ['kamar' => $kamar, 'admin' => $admin] = $this->makeData();

        // Santri lain di luar kamar
        $kamar2 = Kamar::create(['nama_kamar' => 'Kamar B']);
        $out = Santri::create(['nis' => '002', 'nama' => 'Santri Luar', 'kamar_id' => $kamar2->id, 'kelas' => 'Kelas', 'status' => 'Aktif']);

        $this->actingAs($admin);
        $this->post('/pesan', ['pesan' => 'Pesan kamar', 'mode' => 'kamar', 'kamar_id' => $kamar->id])
            ->assertRedirect(route('pesan.index'));

        $this->assertDatabaseHas('admin_messages', ['santri_id' => $kamar->santris()->first()->id, 'pesan' => 'Pesan kamar']);
        $this->assertDatabaseMissing('admin_messages', ['santri_id' => $out->id]);
    }
}