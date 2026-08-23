<?php

namespace App\Http\Controllers;

use App\Models\PresensiLog;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $logs = PresensiLog::with(['santri.kamar', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('santri', fn ($s) => $s->where('nama', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('riwayat.index', compact('logs', 'search'));
    }
}
