<?php

namespace App\Http\Controllers;

use App\Models\KritikSaran;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KritikSaranController extends Controller
{
    private function ensureTable(): void
    {
        if (Schema::hasTable('kritik_saran')) {
            return;
        }

        try {
            Schema::create('kritik_saran', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('pesan');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                $table->index(['is_read', 'created_at']);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (Schema::hasTable('kritik_saran')) {
                return;
            }
            throw $e;
        }
    }

    public function create()
    {
        $this->ensureTable();

        if (Auth::user()->role !== 'pengasuh') {
            abort(403, 'Halaman ini hanya untuk pengasuh.');
        }

        return view('kritiksaran.create');
    }

    public function store(Request $request)
    {
        $this->ensureTable();

        if (Auth::user()->role !== 'pengasuh') {
            abort(403, 'Halaman ini hanya untuk pengasuh.');
        }

        $validated = $request->validate([
            'pesan' => 'required|string|max:1000',
        ]);

        KritikSaran::create([
            'user_id' => Auth::id(),
            'pesan' => trim($validated['pesan']),
        ]);

        \App\Support\AdminNotif::push('kritik', 'Kritik & saran baru dari ' . Auth::user()->name, route('kritik-saran.index'));

        return redirect()
            ->route('kritik-saran.create')
            ->with('success', 'Kritik & saran Anda telah terkirim. Terima kasih atas masukannya!');
    }

    public function index(Request $request)
    {
        $this->ensureTable();

        if (Auth::user()->isAdmin()) {
            \App\Support\AdminNotif::markTypeRead('kritik');
        }

        $status = $request->get('status', '');

        $kritikSarans = KritikSaran::with('user')
            ->when($status === 'belum', fn ($q) => $q->where('is_read', false))
            ->when($status === 'dibaca', fn ($q) => $q->where('is_read', true))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('kritiksaran.index', compact('kritikSarans', 'status'));
    }

    public function markRead(KritikSaran $kritik)
    {
        $this->ensureTable();

        if (!$kritik->is_read) {
            $kritik->update(['is_read' => true]);
        }

        return back()->with('success', 'Kritik & saran ditandai sudah dibaca.');
    }

    public function destroy(KritikSaran $kritik)
    {
        $this->ensureTable();

        $kritik->delete();

        return back()->with('success', 'Kritik & saran telah dihapus.');
    }
}