<?php

namespace App\Http\Controllers;

use App\Models\IzinRequest;
use App\Models\PresensiSholat;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class IzinRequestController extends Controller
{
    public const WAKTU = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];

    public function index(Request $request)
    {
        $status = $request->get('status', '');

        $izinRequests = IzinRequest::with(['santri.kamar', 'user', 'approver'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('izin.index', compact('izinRequests', 'status'));
    }

    public function create()
    {
        $daftarSantri = Santri::orderBy('nama')->get();
        return view('izin.create', compact('daftarSantri'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => 'required|exists:santris,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'waktu_sholat' => 'required|in:all,' . implode(',', self::WAKTU),
            'alasan' => 'required|string|max:500',
        ]);

        IzinRequest::create($validated + ['user_id' => Auth::id()]);

        return redirect()->route('izin.index')
            ->with('success', 'Pengajuan izin berhasil dibuat dan menunggu persetujuan.');
    }

    public function setujui(IzinRequest $izin)
    {
        if ($izin->status !== 'Menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $waktus = $izin->waktu_sholat === 'all'
            ? self::WAKTU
            : [$izin->waktu_sholat];

        $cursor = $izin->tanggal_mulai->copy();
        while ($cursor->lte($izin->tanggal_selesai)) {
            foreach ($waktus as $waktu) {
                PresensiSholat::updateOrCreate(
                    [
                        'santri_id' => $izin->santri_id,
                        'tanggal' => $cursor->format('Y-m-d'),
                        'waktu_sholat' => $waktu,
                    ],
                    [
                        'status' => 'Izin',
                        'catatan' => 'Izin disetujui #' . $izin->id . ': ' . $izin->alasan,
                    ]
                );
            }
            $cursor->addDay();
        }

        $izin->update([
            'status' => 'Disetujui',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan disetujui. Presensi Izin telah dibuat otomatis.');
    }

    public function tolak(IzinRequest $izin)
    {
        if ($izin->status !== 'Menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $izin->update([
            'status' => 'Ditolak',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan izin ditolak.');
    }
}
