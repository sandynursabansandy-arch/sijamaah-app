<?php

namespace Tests\Feature;

use App\Models\AdminMessage;
use App\Models\Kamar;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_user_can_hide_own_message_copy_and_mark_read(): void
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

        // User 1 menyembunyikan salinannya sendiri -> baris tetap ada di DB
        // (hanya user_hidden_at terisi), punya user 2 tidak tersentuh
        $mine = AdminMessage::where('santri_id', $santri->id)->first();
        $this->delete("/pesan-user/{$mine->id}")->assertOk();
        $this->assertDatabaseHas('admin_messages', ['id' => $mine->id]);
        $this->assertNotNull(AdminMessage::find($mine->id)->user_hidden_at);
        $this->assertDatabaseCount('admin_messages', 2);
        $this->assertDatabaseHas('admin_messages', ['santri_id' => $santri2->id, 'user_hidden_at' => null]);

        // Salinan yang disembunyikan hilang dari daftar & badge user 1
        $this->getJson('/pesan-user/daftar')->assertOk()->assertJsonCount(0, 'items');
        $this->getJson('/pesan-user/jumlah')->assertOk()->assertJson(['count' => 0]);
        // Tapi user 2 tetap punya salinannya
        $this->actingAs($pengasuh2);
        $this->getJson('/pesan-user/daftar')->assertOk()->assertJsonCount(1, 'items');

        // User 1 tidak bisa menyembunyikan pesan milik user 2
        $this->actingAs($pengasuh);
        $others = AdminMessage::where('santri_id', $santri2->id)->first();
        $this->delete("/pesan-user/{$others->id}")->assertOk();
        $this->assertNull(AdminMessage::find($others->id)->user_hidden_at);

        // Kirim pesan baru, tandai satu dibaca
        $this->actingAs($admin);
        $this->from('/pesan')->post('/pesan', ['pesan' => 'Pesan dua', 'mode' => 'all']);
        $this->actingAs($pengasuh);
        $unread = AdminMessage::where('santri_id', $santri->id)->whereNull('user_hidden_at')->where('is_read', false)->first();
        $this->post("/pesan-user/{$unread->id}/baca")->assertOk();
        $this->assertDatabaseHas('admin_messages', ['id' => $unread->id, 'is_read' => true]);

        // Admin melihat nama pembaca pada batch multi-penerima yang baru dibaca sebagian
        $this->actingAs($admin);
        $this->get('/pesan')->assertOk()->assertSee('Dibaca oleh 1 santri');
        $html = $this->get('/pesan')->getContent();
        $this->assertStringNotContainsString('Dibaca oleh 2 santri', $html);
    }

    public function test_user_hiding_message_does_not_shrink_admin_stats(): void
    {
        ['santri' => $santri, 'admin' => $admin, 'pengasuh' => $pengasuh] = $this->makeData();

        $kamar2 = Kamar::create(['nama_kamar' => 'Kamar B']);
        $santri2 = Santri::create(['nis' => '009', 'nama' => 'Santri Lain', 'kamar_id' => $kamar2->id, 'kelas' => 'Kelas', 'status' => 'Aktif']);

        $this->actingAs($admin);
        $this->from('/pesan')->post('/pesan', ['pesan' => 'Penting', 'mode' => 'all']);
        $this->assertDatabaseCount('admin_messages', 2);

        // Kedua penerima membaca, lalu yang satu menyembunyikan salinannya
        foreach ([$santri, $santri2] as $s) {
            AdminMessage::where('santri_id', $s->id)->update(['is_read' => true, 'read_at' => now()]);
        }

        $this->actingAs($pengasuh);
        $mine = AdminMessage::where('santri_id', $santri->id)->first();
        $this->delete("/pesan-user/{$mine->id}")->assertOk();

        // Statistik admin tetap utuh: 2 penerima, 2 terbaca, nama keduanya tampil
        $this->actingAs($admin);
        $html = $this->get('/pesan')->assertOk()->getContent();
        $this->assertStringContainsString('Semua dibaca', $html);
        $this->assertStringContainsString('ke 2 santri', $html);
        $this->assertStringContainsString('Santri Pengasuh', $html);
        $this->assertStringContainsString('Santri Lain', $html);
        $this->assertDatabaseCount('admin_messages', 2);
    }

    public function test_user_hiding_unread_message_still_counts_as_unread_for_admin(): void
    {
        ['santri' => $santri, 'admin' => $admin, 'pengasuh' => $pengasuh] = $this->makeData();

        $this->actingAs($admin);
        $this->from('/pesan')->post('/pesan', ['pesan' => 'Belum dibaca', 'mode' => 'all']);

        // Disembunyikan tanpa dibaca -> admin tetap melihat 0/1 terbaca
        $this->actingAs($pengasuh);
        $mine = AdminMessage::where('santri_id', $santri->id)->first();
        $this->delete("/pesan-user/{$mine->id}")->assertOk();

        $this->actingAs($admin);
        $html = $this->get('/pesan')->assertOk()->getContent();
        $this->assertStringContainsString('0/1 dibaca', $html);
    }

    public function test_admin_history_shows_only_latest_50_batches(): void
    {
        ['santri' => $santri, 'admin' => $admin] = $this->makeData();

        // created_at harus bertingkat. Kalau semua seri, urutan 50 batch
        // terakhir jadi tidak deterministik dan test ini flaky.
        $base = now()->subDays(100);
        $rows = [];
        for ($i = 0; $i < 55; $i++) {
            $at = $base->copy()->addSeconds($i);
            $rows[] = [
                'batch_id' => 'batch-'.$i,
                'santri_id' => $santri->id,
                'sent_by' => $admin->id,
                'pesan' => 'Pesan '.$i,
                'is_read' => true,
                'read_at' => $at,
                'created_at' => $at,
                'updated_at' => $at,
            ];
        }
        DB::table('admin_messages')->insert($rows);

        $this->actingAs($admin);
        $html = $this->get('/pesan')->assertOk()->getContent();

        // Tepat 50 batch dirender, bukan 55
        $this->assertSame(50, substr_count($html, 'Dibaca oleh 1 santri'));
        // Batch terbaru ikut tampil, batch tertua (0-4) terpotong
        $this->assertStringContainsString('Pesan 54<', $html);
        $this->assertStringNotContainsString('Pesan 0<', $html);
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