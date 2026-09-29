<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Pesan &mdash; SIJAMAAH</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .alert-animate { animation: slideDown 0.4s ease-out; }
        .transition-smooth { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        .ps-card { animation: slideUp 0.5s ease-out both; }
        .ps-card:nth-child(1) { animation-delay: 0.05s; }
        .ps-card:nth-child(2) { animation-delay: 0.1s; }
        .ps-card:nth-child(3) { animation-delay: 0.15s; }
        .ps-card:nth-child(n+4) { animation-delay: 0.2s; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">

        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5 animate-slide-in">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-teal-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Kirim Pesan</h1>
                <p class="text-[11px] text-slate-400">Pengumuman ke santri melalui Panel Saya</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">{{ session('error') }}</div>
        @endif

        <!-- Kirim Pesan -->
        <form method="POST" action="{{ route('pesan.store') }}" class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 mb-5 animate-slide-in ps-card">
            @csrf
            <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Isi Pesan</label>
            <textarea name="pesan" required rows="3" maxlength="2000" placeholder="Tulis pengumuman untuk santri..." class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 box-border">{{ old('pesan') }}</textarea>
            <p class="text-[10px] text-slate-400 mt-1 pl-0.5 flex items-center justify-between gap-2"><span>Maksimal 2000 karakter.</span><span id="pesanCount">0/2000</span></p>

            <div class="mt-3">
                <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Kirim Ke</label>
                <div class="flex flex-wrap gap-3 text-[13px] text-slate-600">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="mode" value="all" class="cursor-pointer" style="accent-color:#10b981;" onclick="pesanMode('all')" {{ old('mode', 'all') === 'all' ? 'checked' : '' }}>
                        Semua santri aktif
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="mode" value="kamar" class="cursor-pointer" style="accent-color:#10b981;" onclick="pesanMode('kamar')" {{ old('mode') === 'kamar' ? 'checked' : '' }}>
                        Per kamar
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="mode" value="santri" class="cursor-pointer" style="accent-color:#10b981;" onclick="pesanMode('santri')" {{ old('mode') === 'santri' ? 'checked' : '' }}>
                        Santri tertentu
                    </label>
                </div>

                <div id="modeKamarWrap" class="hidden mt-2">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Pilih Kamar</label>
                    <select name="kamar_id" class="w-full border border-slate-200 py-2 px-3 rounded-lg text-[13px] bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-300 box-border">
                        @foreach($kamars as $kamar)
                            <option value="{{ $kamar->id }}" {{ old('kamar_id') == $kamar->id ? 'selected' : '' }}>{{ $kamar->nama_kamar }} ({{ $kamar->santris()->where('status', 'Aktif')->count() }} santri)</option>
                        @endforeach
                    </select>
                </div>

                <div id="modeSantriWrap" class="hidden mt-2">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Pilih Santri</label>
                    <select name="santri_id" class="w-full border border-slate-200 py-2 px-3 rounded-lg text-[13px] bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-300 box-border">
                        @foreach($santris as $santri)
                            <option value="{{ $santri->id }}" {{ old('santri_id') == $santri->id ? 'selected' : '' }}>{{ $santri->nama }} ({{ $santri->kamar?->nama_kamar ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="mt-3 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2 rounded-lg font-semibold text-sm shadow transition-smooth flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Kirim Pesan
            </button>
        </form>

        <!-- Riwayat Pesan -->
        <h2 class="text-[13px] font-bold text-slate-700 mb-2 animate-fade-in">Riwayat Pesan</h2>
        <div class="space-y-3">
            @forelse($list as $m)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 transition-smooth hover:shadow-md ps-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-[13px]">{{ $m['sent_by'] }}</span>
                                @if($m['read'] >= $m['total'] && $m['total'] > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">Semua dibaca</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">{{ $m['read'] }}/{{ $m['total'] }} dibaca</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $m['created_at']->format('d M Y H:i') }} &middot; ke {{ $m['total'] }} santri</p>
                            <p class="text-[13px] text-slate-700 mt-2 break-words">{!! nl2br(e($m['pesan'])) !!}</p>
                            @if(count($m['read_by']) > 0)
                                <details class="mt-2 pt-2 border-t border-slate-100">
                                    <summary class="text-[11px] font-semibold text-slate-500 cursor-pointer select-none hover:text-emerald-600 transition-smooth">Dibaca oleh {{ count($m['read_by']) }} santri</summary>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach($m['read_by'] as $nama)
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-medium">{{ $nama }}</span>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('pesan.destroy', $m['batch_id']) }}" onsubmit="return confirm('Hapus pesan ini untuk semua penerima?')" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg font-semibold text-[11px] shadow transition-smooth">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-10 text-center text-slate-400 text-sm animate-fade-in">
                    Belum ada pesan. Kirim pengumuman pertama Anda.
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function pesanMode(sel) {
            document.getElementById('modeKamarWrap').classList.toggle('hidden', sel !== 'kamar');
            document.getElementById('modeSantriWrap').classList.toggle('hidden', sel !== 'santri');
        }
        pesanMode(document.querySelector('input[name="mode"]:checked') ? document.querySelector('input[name="mode"]:checked').value : 'all');

        var ta = document.querySelector('textarea[name="pesan"]');
        if (ta) {
            ta.addEventListener('input', function () {
                var c = document.getElementById('pesanCount');
                if (c) c.textContent = ta.value.length + '/2000';
            });
        }
    </script>
</body>
</html>