<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SantriController extends Controller
{
    protected function findOrCreateKamar(string $namaKamar): Kamar
    {
        $namaKamar = trim($namaKamar);

        $attributes = ['nama_kamar' => $namaKamar];

        if (Schema::hasTable('kamars') && !Schema::hasColumn('kamars', 'nama_kamar')) {
            if (Schema::hasColumn('kamars', 'nama')) {
                $attributes = ['nama' => $namaKamar];
            } elseif (Schema::hasColumn('kamars', 'kamar')) {
                $attributes = ['kamar' => $namaKamar];
            }
        }

        return Kamar::firstOrCreate(
            $attributes,
            ['wali_kamar' => null, 'deskripsi' => null]
        );
    }

    // Tampilkan daftar santri
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $kamarId = $request->get('kamar_id', '');

        $query = Santri::with('kamar');

        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%');
        }

        if ($kamarId) {
            $query->where('kamar_id', $kamarId);
        }

        $santris = $query->orderBy('nama', 'asc')->paginate(15);
        $daftarKamar = Kamar::all();

        return view('santri.index', compact('santris', 'daftarKamar', 'search', 'kamarId'));
    }

    // Form create santri
    public function create()
    {
        $daftarKamar = Kamar::all();
        return view('santri.create', compact('daftarKamar'));
    }

    // Store santri baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kamar' => 'required|string|max:100',
            'kelas' => 'required|string|max:50',
            'jabatan' => 'nullable|string|max:100',
        ]);

        $kamar = $this->findOrCreateKamar($validated['kamar']);

        Santri::create([
            'nama' => $validated['nama'],
            'kamar_id' => $kamar->id,
            'kelas' => $validated['kelas'],
            'jabatan' => $validated['jabatan'] ?? null,
            'nis' => uniqid('S'),
        ]);

        return redirect()->route('santri.create')
                       ->with('success', 'Santri berhasil ditambahkan! Silakan tambah lagi atau kembali ke daftar.');
    }

    // Form edit santri
    public function edit(Santri $santri)
    {
        $daftarKamar = Kamar::all();
        return view('santri.edit', compact('santri', 'daftarKamar'));
    }

    // Update santri
    public function update(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kamar' => 'required|string|max:100',
            'kelas' => 'required|string|max:50',
            'jabatan' => 'nullable|string|max:100',
        ]);

        $kamar = $this->findOrCreateKamar($validated['kamar']);

        $santri->update([
            'nama' => $validated['nama'],
            'kamar_id' => $kamar->id,
            'kelas' => $validated['kelas'],
            'jabatan' => $validated['jabatan'] ?? null,
        ]);

        // Tetap di halaman edit; kembali ke daftar via tombol "Kembali ke Daftar Santri"
        return redirect()->route('santri.edit', $santri->id)
                       ->with('success', 'Santri berhasil diperbarui!');
    }

    // Delete santri
    public function destroy(Santri $santri)
    {
        $santri->delete();

        return redirect()->route('santri.index')
                       ->with('success', '✅ Santri berhasil dihapus!');
    }

    // Toggle status santri
    public function toggleStatus(Santri $santri)
    {
        // Status otomatis set ke Aktif saat create, jadi feature ini bisa dihapus
        // Atau bisa digunakan untuk fitur aktif/nonaktif di masa depan
        return back();
    }

    // Form import santri dari CSV
    public function importForm()
    {
        $daftarKamar = Kamar::all();
        return view('santri.import', compact('daftarKamar'));
    }

    // Proses import santri dari file CSV
    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if ($handle === false) {
            return back()->with('error', 'File tidak dapat dibaca.');
        }

        // Deteksi delimiter dari baris pertama
        $firstLine = fgets($handle);
        $delimiter = substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',') ? ';' : ',';
        rewind($handle);

        $rows = [];
        while (($raw = fgetcsv($handle, 1000, $delimiter)) !== false) {
            if (count($raw) < 1 || trim(implode('', $raw)) === '') continue;
            $rows[] = array_map(fn ($v) => trim((string) $v), $raw);
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->with('error', 'File CSV kosong.');
        }

        // Lewati header jika baris pertama mengandung kata "nama"
        if (stripos($rows[0][0], 'nama') !== false) {
            array_shift($rows);
        }

        $berhasil = 0; $duplikat = 0; $gagal = 0;

        foreach ($rows as $row) {
            $nama = $row[0] ?? '';
            $kamarNama = $row[1] ?? '';
            $kelas = $row[2] ?? '-';
            $jabatan = $row[3] ?? null;

            if ($nama === '' || $kamarNama === '') { $gagal++; continue; }

            $exists = Santri::where('nama', $nama)
                ->whereHas('kamar', fn ($q) => $q->where('nama_kamar', $kamarNama))
                ->exists();
            if ($exists) { $duplikat++; continue; }

            try {
                $kamar = $this->findOrCreateKamar($kamarNama);
                Santri::create([
                    'nama' => $nama,
                    'kamar_id' => $kamar->id,
                    'kelas' => $kelas !== '' ? $kelas : '-',
                    'jabatan' => $jabatan !== '' ? $jabatan : null,
                    'nis' => uniqid('S'),
                ]);
                $berhasil++;
            } catch (\Throwable $e) {
                $gagal++;
            }
        }

        $pesan = "Import selesai: {$berhasil} santri ditambahkan";
        if ($duplikat > 0) $pesan .= ", {$duplikat} duplikat dilewati";
        if ($gagal > 0) $pesan .= ", {$gagal} baris gagal";

        return redirect()->route('santri.index')->with('success', $pesan . '.');
    }
}
