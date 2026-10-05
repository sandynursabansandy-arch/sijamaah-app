<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengajuan Izin — SANTRI.ID</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .alert-animate { animation: slideDown 0.5s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 mt-5">

        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Edit Pengajuan Izin</h1>
                <p class="text-[11px] text-slate-400">Koreksi data pengajuan izin yang belum diproses</p>
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

        <form method="POST" action="{{ route('izin.update', $izin->id) }}" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4 animate-slide-up">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Santri</label>
                <select name="santri_id" required disabled
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg text-sm bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none">
                    @foreach($daftarSantri as $santri)
                        <option value="{{ $santri->id }}" {{ $izin->santri_id == $santri->id ? 'selected' : '' }}>
                            {{ $santri->nama }} ({{ $santri->kamar?->nama_kamar ?? '-' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Santri tidak dapat diubah</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $izin->tanggal_mulai->format('Y-m-d')) }}" required
                        class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $izin->tanggal_selesai->format('Y-m-d')) }}" required
                        class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Waktu Sholat</label>
                <select name="waktu_sholat"
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">
                    <option value="all" {{ old('waktu_sholat', $izin->waktu_sholat) === 'all' ? 'selected' : '' }}>Semua Waktu Sholat</option>
                    @foreach(['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] as $waktu)
                        <option value="{{ $waktu }}" {{ old('waktu_sholat', $izin->waktu_sholat) === $waktu ? 'selected' : '' }}>{{ $waktu }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">Alasan</label>
                <textarea name="alasan" rows="3" required maxlength="500" placeholder="Contoh: pulang kampung, sakit, acara keluarga..."
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">{{ old('alasan', $izin->alasan) }}</textarea>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('izin.index') }}" class="flex-1 text-center bg-slate-100 text-slate-600 py-2.5 rounded-lg font-semibold text-sm hover:bg-slate-200 transition">Batal</a>
                <button type="submit" class="flex-1 bg-gradient-to-r from-teal-500 to-teal-600 text-white py-2.5 rounded-lg font-semibold text-sm shadow-lg hover:opacity-90 transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>

