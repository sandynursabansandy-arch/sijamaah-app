<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kritik &amp; Saran &mdash; SIJAMAAH</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInFast { from { opacity: 0; transform: translateY(-6px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .alert-animate { animation: slideDown 0.4s ease-out; }
        .transition-smooth { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        .ks-card { animation: slideUp 0.5s ease-out both; }
        .ks-card:nth-child(1) { animation-delay: 0.05s; }
        .ks-card:nth-child(2) { animation-delay: 0.1s; }
        .ks-card:nth-child(3) { animation-delay: 0.15s; }
        .ks-card:nth-child(4) { animation-delay: 0.2s; }
        .ks-card:nth-child(5) { animation-delay: 0.25s; }
        .ks-card:nth-child(n+6) { animation-delay: 0.3s; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">

        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5 animate-slide-in">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Kelola Kritik &amp; Saran</h1>
                <p class="text-[11px] text-slate-400">Masukan dari para pengasuh santri</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">{{ session('error') }}</div>
        @endif

        <!-- Filter status -->
        <form method="GET" action="{{ route('kritik-saran.index') }}" class="bg-white rounded-xl p-3 mb-5 shadow-sm border border-slate-100 flex flex-wrap items-end gap-2.5 animate-fade-in">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Status</label>
                <select name="status" onchange="this.form.submit()" class="border border-slate-200 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-[13px] bg-slate-50 h-[34px] box-border focus:outline-none">
                    <option value="">Semua</option>
                    <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum dibaca</option>
                    <option value="dibaca" {{ $status === 'dibaca' ? 'selected' : '' }}>Sudah dibaca</option>
                </select>
            </div>
        </form>

        <!-- List -->
        <div class="space-y-3">
            @forelse($kritikSarans as $ks)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 transition-smooth hover:shadow-md ks-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-extrabold text-sm ring-2 ring-emerald-100 select-none">
                                {{ strtoupper(substr($ks->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-[15px]">{{ $ks->user?->name ?? 'Pengguna' }}</span>
                                    @if($ks->is_read)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Sudah dibaca</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">Belum dibaca</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $ks->created_at->format('d M Y H:i') }}</p>
                                <p class="text-[13px] text-slate-700 mt-2 break-words">{!! nl2br(e($ks->pesan)) !!}</p>
                            </div>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            @if(!$ks->is_read)
                                <form method="POST" action="{{ route('kritik-saran.markRead', $ks->id) }}">
                                    @csrf
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg font-semibold text-[11px] shadow transition-smooth">Tandai dibaca</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('kritik-saran.destroy', $ks->id) }}" onsubmit="return confirm('Hapus kritik &amp; saran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg font-semibold text-[11px] shadow transition-smooth">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-10 text-center text-slate-400 text-sm animate-fade-in">
                    Belum ada kritik &amp; saran.
                </div>
            @endforelse
        </div>

        @if($kritikSarans->hasPages())
            <div class="mt-4 flex justify-center">
                {{ $kritikSarans->links() }}
            </div>
        @endif
    </div>
</body>
</html>