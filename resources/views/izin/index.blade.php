<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-800 mb-3 text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Dashboard
                </a>
                <h1 class="text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                    Pengajuan Izin
                </h1>
                <p class="text-slate-500 text-xs">Ajukan izin santri; jika disetujui, presensi "Izin" dibuat otomatis</p>
            </div>
            @if(auth()->user()?->canManagePresensi())
                <a href="{{ route('izin.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm shadow-lg hover:opacity-90 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ajukan Izin
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-4 py-2.5 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm">{{ session('error') }}</div>
        @endif

        <!-- Filter status -->
        <form method="GET" action="{{ route('izin.index') }}" class="bg-white rounded-xl p-3 mb-5 shadow-sm border border-slate-100 flex flex-wrap items-end gap-2.5">
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
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
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
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold text-xs shadow transition">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('izin.tolak', $izin->id) }}" onsubmit="return confirm('Tolak pengajuan ini?')">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold text-xs shadow transition">Tolak</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-10 text-center text-slate-400 text-sm">
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
</body>
</html>
