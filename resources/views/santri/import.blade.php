<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Santri dari CSV</title>
    <link rel="stylesheet" href="{{ asset('custom-assets/app.css') }}?v={{ filemtime(public_path('build/assets/app-B60Kc5GY.css')) }}">
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('santri.index') }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-800 mb-3 text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Kelola Santri
            </a>
            <h1 class="text-xl sm:text-2xl md:text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                Import Santri
            </h1>
            <p class="text-slate-500 text-xs">Tambah banyak santri sekaligus dari file CSV</p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-4 py-2.5 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm">{{ session('error') }}</div>
        @endif

        <!-- Format -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5">
            <h2 class="font-bold text-sm text-blue-800 mb-2">Format File CSV</h2>
            <p class="text-[13px] text-blue-700 mb-2">Kolom (dengan atau tanpa baris header), pemisah koma <code class="bg-blue-100 px-1 rounded">,</code> atau titik-koma <code class="bg-blue-100 px-1 rounded">;</code>:</p>
            <pre class="text-[12px] bg-white border border-blue-100 rounded-lg p-3 overflow-x-auto leading-relaxed"><code>nama,rayon,kelas,jabatan
Ahmad Fauzi,Rayon 1,3 Ali,Amir
Budi Santoso,Rayon 2,2 Ulya,</code></pre>
            <ul class="text-[12px] text-blue-700 mt-2 list-disc list-inside space-y-0.5">
                <li><strong>nama</strong> dan <strong>rayon</strong> wajib diisi</li>
                <li><strong>kelas</strong> dan <strong>jabatan</strong> opsional</li>
                <li>Rayon baru akan dibuat otomatis jika belum ada</li>
                <li>Data duplikat (nama + rayon sama) akan dilewati</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('santri.import.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">File CSV</label>
                <input type="file" name="file" accept=".csv,text/csv" required
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg text-sm bg-slate-50 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-emerald-500 file:text-white file:text-xs file:font-semibold cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-300">
            </div>

            <button type="submit" onclick="return confirm('Yakin ingin import santri dari file ini?')"
                class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 text-white py-2.5 rounded-lg font-semibold text-sm shadow-lg hover:opacity-90 transition">
                Import Sekarang
            </button>
        </form>
    </div>
</body>
</html>
