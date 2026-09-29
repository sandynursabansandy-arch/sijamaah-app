<?php

namespace App\Http\Controllers;

use App\Models\AdminMessage;
use App\Models\Kamar;
use App\Models\Santri;
use App\Support\AdminPesan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PesanController extends Controller
{
    protected function userSantriId(): ?int
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $santri = Santri::where('nama', $user->name)->where('status', 'Aktif')->first();

        return $santri?->id;
    }

    public function index(Request $request)
    {
        AdminPesan::ensureTable();

        $santris = Santri::where('status', 'Aktif')->orderBy('nama')->get();
        $kamars = Kamar::all();

        $list = [];
        AdminMessage::with(['sender', 'santri'])->orderByDesc('created_at')->get()
            ->groupBy('batch_id')
            ->each(function ($rows, $batchId) use (&$list) {
                $first = $rows->first();
                $readRows = $rows->where('is_read', true);
                $list[] = [
                    'batch_id' => $batchId,
                    'pesan' => $first->pesan,
                    'created_at' => $first->created_at,
                    'sent_by' => $first->sender?->name ?? 'Admin',
                    'total' => $rows->count(),
                    'read' => $readRows->count(),
                    'read_by' => $readRows->pluck('santri.nama')
                        ->filter(fn ($n) => !empty($n))
                        ->values()
                        ->all(),
                ];
            });

        $list = array_slice(array_values($list), 0, 50);

        return view('pesan.index', compact('santris', 'kamars', 'list'));
    }

    public function store(Request $request)
    {
        AdminPesan::ensureTable();

        $request->validate([
            'pesan' => ['required', 'string', 'max:2000'],
            'mode' => ['required', 'in:all,kamar,santri'],
            'kamar_id' => ['nullable', 'exists:kamars,id'],
            'santri_id' => ['nullable', 'exists:santris,id'],
        ]);

        if ($request->mode === 'all') {
            $targets = Santri::where('status', 'Aktif')->pluck('id')->all();
        } elseif ($request->mode === 'kamar') {
            $targets = Santri::where('status', 'Aktif')->where('kamar_id', $request->kamar_id)->pluck('id')->all();
        } else {
            $targets = Santri::where('id', $request->santri_id)->where('status', 'Aktif')->pluck('id')->all();
        }

        if (count($targets) === 0) {
            return back()->with('error', 'Tidak ada santri aktif yang dipilih.');
        }

        $batchId = (string) Str::uuid();
        foreach ($targets as $sid) {
            AdminMessage::create([
                'batch_id' => $batchId,
                'santri_id' => $sid,
                'sent_by' => auth()->id(),
                'pesan' => $request->pesan,
                'is_read' => false,
            ]);
        }

        return redirect()->route('pesan.index')->with('success', 'Pesan dikirim ke '.count($targets).' santri.');
    }

    public function destroy(Request $request, string $batchId)
    {
        AdminPesan::ensureTable();
        AdminMessage::where('batch_id', $batchId)->delete();

        return redirect()->route('pesan.index')->with('success', 'Pesan dihapus.');
    }

    public function userJumlah(Request $request)
    {
        AdminPesan::ensureTable();
        $santriId = $this->userSantriId();
        $count = $santriId ? AdminMessage::where('santri_id', $santriId)->where('is_read', false)->count() : 0;

        return response()->json(['count' => $count]);
    }

    public function userDaftar(Request $request)
    {
        AdminPesan::ensureTable();
        $items = [];
        $santriId = $this->userSantriId();

        if ($santriId) {
            $messages = AdminMessage::where('santri_id', $santriId)->orderByDesc('created_at')->orderByDesc('id')->limit(20)->get();
            foreach ($messages as $m) {
                $items[] = [
                    'id' => $m->id,
                    'type' => 'pesan',
                    'title' => 'Pesan dari Admin',
                    'text' => $m->pesan,
                    'created_at' => $m->created_at?->format('Y-m-d H:i:s'),
                    'is_read' => (bool) $m->is_read,
                    'url' => route('panel-saya.index').'#pesan-admin',
                ];
            }
        }

        return response()->json(['items' => $items]);
    }

    public function userMarkAllRead(Request $request)
    {
        AdminPesan::ensureTable();
        $santriId = $this->userSantriId();

        if ($santriId) {
            AdminMessage::where('santri_id', $santriId)->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function userMarkRead(Request $request, $id)
    {
        AdminPesan::ensureTable();
        $santriId = $this->userSantriId();

        if ($santriId) {
            AdminMessage::where('id', $id)->where('santri_id', $santriId)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function userDestroy(Request $request, $id)
    {
        AdminPesan::ensureTable();
        $santriId = $this->userSantriId();

        if ($santriId) {
            AdminMessage::where('id', $id)->where('santri_id', $santriId)
                ->limit(1)->delete();
        }

        return response()->json(['ok' => true]);
    }
}