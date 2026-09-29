<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    public function index()
    {
        $kamars = Kamar::withCount('santris')->orderBy('nama_kamar')->get();
        return view('kamar.index', compact('kamars'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kamar' => 'required|string|max:255|unique:kamars,nama_kamar',
            'wali_kamar' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
        ], [
            'nama_kamar.required' => 'Nama kamar wajib diisi.',
            'nama_kamar.unique' => 'Nama kamar sudah ada.',
        ]);

        Kamar::create($data);

        return back()->with('success', 'Kamar "' . $data['nama_kamar'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Kamar $kamar)
    {
        $data = $request->validate([
            'nama_kamar' => 'required|string|max:255|unique:kamars,nama_kamar,' . $kamar->id,
            'wali_kamar' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
        ], [
            'nama_kamar.required' => 'Nama kamar wajib diisi.',
            'nama_kamar.unique' => 'Nama kamar sudah ada.',
        ]);

        $kamar->update($data);

        return back()->with('success', 'Kamar "' . $kamar->nama_kamar . '" berhasil diperbarui.');
    }

    public function destroy(Kamar $kamar)
    {
        if ($kamar->santris()->exists()) {
            return back()->withErrors(['error' => 'Kamar "' . $kamar->nama_kamar . '" masih memiliki santri. Pindahkan dulu santrinya sebelum menghapus kamar.']);
        }

        $nama = $kamar->nama_kamar;
        $kamar->delete();

        return back()->with('success', 'Kamar "' . $nama . '" berhasil dihapus.');
    }
}