<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Izin — SIJAMAAH</title>
    <link rel="stylesheet" href="{{ asset('custom-assets/app.css') }}?v={{ filemtime(public_path('custom-assets/app.css')) }}">
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .animate-slide-in { animation: slideIn 0.5s ease-out both; }
        .alert-animate { animation: slideDown 0.5s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 mt-5">

        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Ajukan Izin</h1>
                <p class="text-[11px] text-slate-400">Pengajuan akan diverifikasi sebelum presensi Izin dibuat</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('izin.store') }}" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4 animate-slide-up">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Santri</label>
                <select name="santri_id" required
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">
                    <option value="">-- Pilih Santri --</option>
                    @foreach($daftarSantri as $santri)
                        <option value="{{ $santri->id }}" {{ old('santri_id') == $santri->id ? 'selected' : '' }}>
                            {{ $santri->nama }} ({{ $santri->kamar?->nama_kamar ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required
                        class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required
                        class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Waktu Sholat</label>
                <select name="waktu_sholat"
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">
                    <option value="all">Semua Waktu Sholat</option>
                    @foreach(['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] as $waktu)
                        <option value="{{ $waktu }}" {{ old('waktu_sholat') === $waktu ? 'selected' : '' }}>{{ $waktu }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Alasan</label>
                <textarea name="alasan" rows="3" required maxlength="500" placeholder="Contoh: pulang kampung, sakit, acara keluarga..."
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">{{ old('alasan') }}</textarea>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 text-white py-2.5 rounded-lg font-semibold text-sm shadow-lg hover:opacity-90 transition">
                Kirim Pengajuan
            </button>
        </form>
    </div>
</body>
</html>
