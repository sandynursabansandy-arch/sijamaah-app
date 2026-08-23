<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Perubahan Presensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-800 mb-3 text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Dashboard
            </a>
            <h1 class="text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                Riwayat Perubahan Presensi
            </h1>
            <p class="text-slate-500 text-xs">Audit log semua perubahan status presensi santri</p>
        </div>

        <!-- Search -->
        <form method="GET" action="{{ route('riwayat.index') }}" class="bg-white rounded-xl p-3 mb-5 shadow-sm border border-slate-100 flex flex-wrap items-end gap-2.5">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Cari Nama Santri</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama santri..."
                    class="w-full border border-slate-200 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 text-[13px] bg-slate-50 h-[34px] box-border focus:outline-none">
            </div>
            <button type="submit" class="bg-emerald-500 text-white py-1.5 px-4 rounded-md font-semibold hover:bg-emerald-600 shadow text-[13px] h-[34px] box-border">Cari</button>
        </form>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Waktu</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Santri</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Tanggal Presensi</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Sholat</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Perubahan</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-slate-500">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-2.5 font-semibold text-[13px]">{{ $log->santri?->nama ?? '-' }}
                                    <span class="block text-[11px] text-slate-400 font-normal">{{ $log->santri?->kamar?->nama_kamar ?? '' }}</span>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-[13px]">{{ $log->tanggal->format('d M Y') }}</td>
                                <td class="px-4 py-2.5 text-[13px]">{{ $log->waktu_sholat }}</td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @php
                                        $warna = ['Jamaah' => 'bg-emerald-100 text-emerald-700', 'Masbuq' => 'bg-amber-100 text-amber-700', 'Izin' => 'bg-blue-100 text-blue-700', 'Alfa' => 'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 text-[12px]">
                                        @if($log->status_lama)
                                            <span class="px-1.5 py-0.5 rounded {{ $warna[$log->status_lama] ?? 'bg-slate-100 text-slate-600' }}">{{ $log->status_lama }}</span>
                                        @else
                                            <span class="text-slate-400">(kosong)</span>
                                        @endif
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        @if($log->status_baru)
                                            <span class="px-1.5 py-0.5 rounded {{ $warna[$log->status_baru] ?? 'bg-slate-100 text-slate-600' }}">{{ $log->status_baru }}</span>
                                        @else
                                            <span class="text-slate-400">(kosong)</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-[12px] text-slate-500">{{ $log->aksi }}</td>
                                <td class="px-4 py-2.5 text-[13px]">{{ $log->user?->name ?? 'Sistem' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400 text-sm">
                                    Belum ada riwayat perubahan presensi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
            <div class="mt-4 flex justify-center">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</body>
</html>
