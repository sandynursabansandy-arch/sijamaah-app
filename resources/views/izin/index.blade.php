<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin — SIJAMAAH</title>
    <link rel="stylesheet" href="{{ asset('custom-assets/app.css') }}?v={{ filemtime(public_path('custom-assets/app.css')) }}">
    <style>
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out both; }
        .transition-smooth { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .alert-animate { animation: slideDown 0.4s ease-out; }

        .izin-card { animation: slideUp 0.5s ease-out both; }
        .izin-card:nth-child(1) { animation-delay: 0.05s; }
        .izin-card:nth-child(2) { animation-delay: 0.1s; }
        .izin-card:nth-child(3) { animation-delay: 0.15s; }
        .izin-card:nth-child(4) { animation-delay: 0.2s; }
        .izin-card:nth-child(5) { animation-delay: 0.25s; }
        .izin-card:nth-child(n+6) { animation-delay: 0.3s; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">

        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5 animate-slide-in">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Pengajuan Izin</h1>
                <p class="text-[11px] text-slate-400">Ajukan izin santri; jika disetujui, presensi "Izin" dibuat otomatis</p>
            </div>
            @if(auth()->user()?->canManagePresensi())
                <a href="{{ route('izin.create') }}" class="shrink-0 inline-flex items-center gap-1.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-4 py-2 rounded-lg font-semibold text-xs shadow-lg hover:opacity-90 transition-smooth">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ajukan Izin
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">{{ session('error') }}</div>
        @endif

        <!-- Filter status -->
        <form method="GET" action="{{ route('izin.index') }}" class="bg-white rounded-xl p-3 mb-5 shadow-sm border border-slate-100 flex flex-wrap items-end gap-2.5 animate-fade-in">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Status</label>
                <select name="status" onchange="this.form.submit()" class="border border-slate-200 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-[13px] bg-slate-50 h-[34px] box-border focus:outline-none">
                    <option value="">Semua</option>
                    <option value="Menunggu" {{ $status === 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="Disetujui" {{ $status === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Ditolak" {{ $status === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
        </form>

        <!-- List -->
        <div class="space-y-3">
            @forelse($izinRequests as $izin)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 transition-smooth hover:shadow-md izin-card">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-[15px]">{{ $izin->santri->nama ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $izin->santri?->kamar?->nama_kamar ?? '' }}</span>
                                @php
                                    $badge = ['Menunggu' => 'bg-amber-100 text-amber-700', 'Disetujui' => 'bg-emerald-100 text-emerald-700', 'Ditolak' => 'bg-red-100 text-red-700'];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge[$izin->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $izin->status }}</span>
                            </div>
                            <p class="text-[13px] text-slate-600 mt-1">
                                <span class="font-semibold">{{ $izin->tanggal_mulai->format('d M Y') }}</span>
                                @if(!$izin->tanggal_mulai->isSameDay($izin->tanggal_selesai))
                                    &ndash; <span class="font-semibold">{{ $izin->tanggal_selesai->format('d M Y') }}</span>
                                @endif
                                &bull; {{ $izin->waktu_sholat === 'all' ? 'Semua waktu sholat' : $izin->waktu_sholat }}
                            </p>
                            <p class="text-[13px] text-slate-500 mt-1 italic">&ldquo;{{ $izin->alasan }}&rdquo;</p>
                            <p class="text-[11px] text-slate-400 mt-1.5">
                                Diajukan oleh {{ $izin->user?->name ?? '-' }} pada {{ $izin->created_at->format('d M Y H:i') }}
                                @if($izin->approved_at)
                                    &bull; Diproses oleh {{ $izin->approver?->name ?? '-' }} pada {{ $izin->approved_at->format('d M Y H:i') }}
                                @endif
                            </p>
                        </div>
                        @if($izin->status === 'Menunggu' && auth()->user()?->canManagePresensi())
                            <div class="flex gap-2 shrink-0">
                                <form method="POST" action="{{ route('izin.setujui', $izin->id) }}" onsubmit="return confirm('Setujui pengajuan ini? Presensi Izin akan dibuat otomatis.')">
                                    @csrf
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold text-xs shadow transition-smooth">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('izin.tolak', $izin->id) }}" onsubmit="return confirm('Tolak pengajuan ini?')">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold text-xs shadow transition-smooth">Tolak</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-10 text-center text-slate-400 text-sm animate-fade-in">
                    Belum ada pengajuan izin.
                </div>
            @endforelse
        </div>

        @if($izinRequests->hasPages())
            <div class="mt-4 flex justify-center">
                {{ $izinRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[100]">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 animate-slide-up">
            <div class="p-6 text-center">
                <div class="mx-auto w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">Keluar dari web?</h3>
                <p class="text-sm text-slate-500 mb-6">Apakah Anda yakin ingin keluar dari aplikasi ini?</p>
                <div class="flex gap-3">
                    <button onclick="closeLogoutModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all duration-200">Batal</button>
                    <button onclick="confirmLogout()" class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition-all duration-200 shadow-md shadow-red-200">Ya, Keluar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showLogoutModal() {
            var modal = document.getElementById('logoutModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        function closeLogoutModal() {
            var modal = document.getElementById('logoutModal');
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '0';
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.opacity = '';
                modal.style.transition = '';
                document.body.style.overflow = '';
            }, 300);
        }
        function confirmLogout() {
            document.getElementById('logoutForm').submit();
        }
    </script>
</body>
</html>
