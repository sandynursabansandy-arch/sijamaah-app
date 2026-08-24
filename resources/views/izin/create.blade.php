<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Izin</title>
    <link rel="stylesheet" href="{{ asset('custom-assets/app.css') }}?v={{ filemtime(public_path('custom-assets/app.css')) }}">
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('izin.index') }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-800 mb-3 text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Daftar Izin
            </a>
            <h1 class="text-xl sm:text-2xl md:text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                Ajukan Izin
            </h1>
            <p class="text-slate-500 text-xs">Pengajuan akan diverifikasi sebelum presensi Izin dibuat</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('izin.store') }}" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4">
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
