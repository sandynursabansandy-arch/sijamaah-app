<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\PresensiSholat;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class InputCepatController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $waktu = $request->get('waktu_sholat', 'Subuh');
        $kamarId = $request->get('kamar_id', 'all');

        $daftarKamar = Kamar::all();

        $query = Santri::query();
        if ($kamarId !== 'all' && $kamarId !== '') {
            $query->where('kamar_id', $kamarId);
        }
        if (Schema::hasColumn('santris', 'status')) {
            $query->where('status', 'Aktif');
        }

        $santris = $query->with(['presensis' => fn ($q) => $q
            ->where('tanggal', $tanggal)
            ->where('waktu_sholat', $waktu)])
            ->orderBy('nama')
            ->get()
            ->map(function ($santri) {
                $santri->status_hari_ini = $santri->presensis->first()?->status ?? '';
                return $santri;
            });

        return view('input-cepat.index', compact(
            'tanggal', 'waktu', 'kamarId', 'daftarKamar', 'santris'
        ));
    }
}
