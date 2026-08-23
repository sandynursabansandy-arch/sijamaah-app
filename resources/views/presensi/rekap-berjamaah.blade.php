<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rekap Berjamaah — SIJAMAAH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .transition-smooth { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15); }
        .filter-gradient { background: #ffffff; border: 1px solid #e2e8f0; }
        .animate-fade-in-fast { animation: fadeInFast 0.15s ease-out both; }
        @keyframes fadeInFast { from { opacity: 0; transform: translateY(-6px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        tbody tr { animation: slideUp 0.5s ease-out; }
        tbody tr:nth-child(1) { animation-delay: 0s; }
        tbody tr:nth-child(2) { animation-delay: 0.03s; }
        tbody tr:nth-child(3) { animation-delay: 0.06s; }
        tbody tr:nth-child(4) { animation-delay: 0.09s; }
        tbody tr:nth-child(5) { animation-delay: 0.12s; }
        tbody tr:nth-child(n+6) { animation-delay: 0.15s; }

        .rekap-scroll { max-height: 600px; overflow: auto; scrollbar-width: thin; scrollbar-color: #10b981 #e2e8f0; }
        .rekap-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .rekap-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 9999px; }
        .rekap-scroll::-webkit-scrollbar-thumb { background: #10b981; border-radius: 9999px; }

        .rekap-scroll thead tr {
            position: sticky; top: 0; z-index: 20;
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .rekap-scroll thead th {
            background: transparent; color: #ffffff; font-weight: 700;
            border-bottom: 2px solid rgba(255,255,255,0.25);
        }
        .rekap-scroll thead th.sticky-corner {
            position: sticky; left: 0; z-index: 30;
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .rekap-scroll thead th.stickyleft {
            position: sticky; left: 0; z-index: 25;
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .rekap-scroll tbody td.stickyleft {
            position: sticky; left: 0; z-index: 10;
            background: #ffffff; box-shadow: 3px 0 6px rgba(0,0,0,0.06);
        }
        .rekap-scroll tbody td.sticky-corner-left {
            position: sticky; left: 0; z-index: 15;
            background: #ffffff; box-shadow: 3px 0 6px rgba(0,0,0,0.06);
        }
        .rekap-scroll tbody td.stickyright {
            position: sticky; z-index: 5;
            background: #ffffff; box-shadow: -3px 0 6px rgba(0,0,0,0.06);
        }
        .rekap-scroll tbody td.sticky-corner-right {
            position: sticky; z-index: 5;
            background: #ffffff; box-shadow: -3px 0 6px rgba(0,0,0,0.06);
        }

        .th-badge { display: inline-block; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.35); border-radius: 6px; padding: 3px 10px; font-size: 11px; font-weight: 700; white-space: nowrap; color: #ffffff; }

        .status-jamaah { background: #dcfce7; color: #166534; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 5px; }
        .status-masbuq { background: #fff7ed; color: #c2410c; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 5px; }
        .status-izin { background: #dbeafe; color: #0c4a6e; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 5px; }
        .status-alfa { background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 5px; }
        .status-none { color: #cbd5e1; font-size: 11px; }

        /* Editable cells */
        td[data-editable] { cursor: pointer; }
        .editable-cell { display: inline-flex; transition: transform 0.15s ease, box-shadow 0.15s ease; border-radius: 5px; }
        td[data-editable]:hover .editable-cell { transform: scale(1.18); box-shadow: 0 2px 8px rgba(0,0,0,0.18); }
        .is-viewonly td[data-editable] { cursor: not-allowed; }
        .is-viewonly .editable-cell { opacity: 0.55; }
        .is-viewonly td[data-editable]:hover .editable-cell { transform: none; box-shadow: none; }

        /* Lock toggle */
        .lock-toggle {
            display: flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700;
            cursor: pointer; transition: all 0.3s ease; border: 1.5px solid transparent;
            user-select: none;
        }
        .lock-toggle.unlocked { background: #f0fdf4; color: #166534; border-color: #86efac; }
        .lock-toggle.unlocked:hover { background: #dcfce7; box-shadow: 0 0 0 2px rgba(34,197,94,0.15); }
        .lock-toggle.locked { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
        .lock-toggle.locked:hover { background: #fee2e2; box-shadow: 0 0 0 2px rgba(239,68,68,0.15); }
        .lock-toggle svg { width: 14px; height: 14px; flex-shrink: 0; }

        .view-only-banner {
            display: none; align-items: center; justify-content: center; gap: 8px;
            padding: 8px 16px; margin-bottom: 12px; border-radius: 10px;
            font-size: 12px; font-weight: 600; color: #92400e;
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border: 1px solid #fde68a;
        }
        .view-only-banner.show { display: flex; }
        .view-only-banner svg { width: 16px; height: 16px; color: #d97706; flex-shrink: 0; }

        /* Status picker buttons */
        .stbtn {
            padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700;
            border: 1.5px solid #e2e8f0; background: #ffffff; color: #64748b;
            cursor: pointer; transition: all 0.15s ease;
        }
        .stbtn:hover { transform: translateY(-1px); border-color: #cbd5e1; }
        .stbtn[data-status="Jamaah"].active { background: #dcfce7; color: #166534; border-color: #22c55e; }
        .stbtn[data-status="Masbuq"].active { background: #fff7ed; color: #c2410c; border-color: #f97316; }
        .stbtn[data-status="Izin"].active { background: #dbeafe; color: #0c4a6e; border-color: #3b82f6; }
        .stbtn[data-status="Alfa"].active { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
        .stbtn[data-status="-"].active { background: #f1f5f9; color: #334155; border-color: #94a3b8; }

        /* Toast */
        #toast-container { position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 9999; pointer-events: none; }
        .toast {
            pointer-events: auto;
            display: flex; align-items: center; gap: 10px;
            padding: 14px 24px; border-radius: 12px;
            font-size: 14px; font-weight: 600; color: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.22);
            animation: toastInBJ 0.35s ease-out forwards;
            max-width: 420px;
        }
        .toast.success { background: #059669; }
        .toast.error   { background: #dc2626; }
        .toast.fade-out { animation: toastOutBJ 0.3s ease-in forwards; }
        @keyframes toastInBJ { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOutBJ { from { opacity:1; transform:translateY(0) scale(1); } to { opacity:0; transform:translateY(-20px) scale(0.95); } }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-5 animate-slide-in relative z-30">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-white shadow-lg flex items-center justify-center overflow-hidden border-[3px] border-white ring-2 ring-emerald-200">
                        <img src="{{ asset('images/image.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h1 class="text-[28px] leading-tight font-extrabold text-emerald-700">
                            Rekap Berjamaah Bulanan
                        </h1>
                        <p class="text-slate-500 text-xs">Detail kehadiran sholat berjamaah setiap santri per hari &middot; {{ $tanggalMulai->translatedFormat('F Y') }}@if($waktuFilter !== 'all') &middot; {{ $waktuFilter }}@endif</p>
                    </div>
                </div>
                <div class="flex gap-2 items-center">
                    <a href="{{ route('presensi.index') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Dashboard</a>
                    <a href="{{ route('presensi.rekap') }}" class="bg-purple-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Rekap Presensi</a>
                    <a href="{{ route('presensi.rankingBerjamaah') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Ranking Berjamaah</a>
                    <a href="{{ route('presensi.rankingAlfa') }}" class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Ranking Alfa</a>
                    @if(auth()->user()?->canManagePresensi())
                        <a href="{{ route('santri.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Kelola Santri</a>
                    @endif
                    <div class="relative" id="settingsRB">
                        <button onclick="toggleDropdown('settingsRB')" class="bg-slate-600 text-white px-3 py-2 rounded-lg font-semibold hover:bg-slate-700 shadow text-xs transition-smooth flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="hidden sm:inline">Akun</span>
                        </button>
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-50 animate-fade-in-fast">
                            <a href="{{ route('password.change') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Ganti Password
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="button" onclick="showLogoutModal()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('presensi.rekapBerjamaah') }}" class="filter-gradient rounded-xl p-3 mb-5 animate-fade-in shadow-sm">
            <div class="flex flex-wrap items-end gap-2.5">
                <div class="animate-slide-in">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Bulan</label>
                    <input type="month" name="bulan" value="{{ $bulan }}" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                </div>
                <div class="animate-slide-in" style="animation-delay: 0.05s">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Asrama / Rayon</label>
                    <select name="kamar_id" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                        <option value="all" {{ $kamarId == 'all' ? 'selected' : '' }}>Semua Rayon</option>
                        @foreach($daftarKamar as $kamar)
                            <option value="{{ $kamar->id }}" {{ $kamarId == $kamar->id ? 'selected' : '' }}>{{ $kamar->nama_kamar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="animate-slide-in" style="animation-delay: 0.1s">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Waktu Sholat</label>
                    <select name="waktu" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                        <option value="all" {{ $waktuFilter == 'all' ? 'selected' : '' }}>Semua Waktu</option>
                        @foreach(['Subuh','Dzuhur','Ashar','Maghrib','Isya'] as $w)
                            <option value="{{ $w }}" {{ $waktuFilter == $w ? 'selected' : '' }}>{{ $w }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ml-auto animate-slide-in flex gap-2" style="animation-delay: 0.1s">
                    @if(auth()->user()?->canManagePresensi())
                        <a href="{{ route('presensi.rekapBerjamaah.export', request()->only(['bulan', 'kamar_id', 'waktu'])) }}"
                           class="bg-blue-500 text-white py-1.5 px-4 rounded-md font-semibold hover:bg-blue-600 shadow transition-smooth text-[13px] h-[34px] box-border inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Excel/CSV
                        </a>
                    @endif
                    <button type="submit" class="bg-emerald-500 text-white py-1.5 px-5 rounded-md font-semibold hover:bg-emerald-600 shadow transition-smooth text-[13px] h-[34px] box-border">Tampilkan</button>
                </div>
            </div>
        </form>

        @if(count($rekapData) > 0)
            @php
                $totalHadir = $rekapData->sum('hadir');
                $totalMasbuq = $rekapData->sum('masbuq');
                $totalIzin = $rekapData->sum('izin');
                $totalAlfa = $rekapData->sum('alfa');
                $totalJadwalAll = $rekapData->sum('total_jadwal');
                $rataRata = $rekapData->count() > 0 ? round($rekapData->avg('persentase'), 1) : 0;
                $numDates = count($allDates);
                $labelStep = max(1, ceil($numDates / 31));
                $stateForJs = $rekapData->mapWithKeys(function ($s) {
                    return [$s['id'] => collect($s['hariDetail'])->mapWithKeys(fn ($h) => [$h['date'] => $h['statuses']])->toArray()];
                })->toArray();
                $namesForJs = $rekapData->mapWithKeys(fn ($s) => [$s['id'] => $s['nama']])->toArray();
                $dateLabelsForJs = collect($allDates)->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => $d->isoFormat('ddd, D MMM')])->toArray();
            @endphp

            <!-- Summary -->
            <div class="grid grid-cols-5 gap-3 mb-5 animate-slide-up">
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 text-center">
                    <p id="sum-hadir" class="text-2xl font-extrabold text-emerald-600">{{ $totalHadir }}</p>
                    <p class="text-[10px] text-slate-500 font-semibold uppercase">Total Berjamaah</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 text-center">
                    <p id="sum-masbuq" class="text-2xl font-extrabold text-orange-500">{{ $totalMasbuq }}</p>
                    <p class="text-[10px] text-slate-500 font-semibold uppercase">Total Masbuq</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 text-center">
                    <p id="sum-izin" class="text-2xl font-extrabold text-blue-500">{{ $totalIzin }}</p>
                    <p class="text-[10px] text-slate-500 font-semibold uppercase">Total Izin</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 text-center">
                    <p id="sum-alfa" class="text-2xl font-extrabold text-red-600">{{ $totalAlfa }}</p>
                    <p class="text-[10px] text-slate-500 font-semibold uppercase">Total Alfa</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 text-center">
                    <p id="sum-rata" class="text-2xl font-extrabold text-emerald-600">{{ $rataRata }}%</p>
                    <p class="text-[10px] text-slate-500 font-semibold uppercase">Rata-rata</p>
                </div>
            </div>

            <!-- Kontrol Edit & Kunci -->
            <div class="flex flex-wrap items-center gap-2 mb-3 animate-slide-up">
                @if(auth()->user()?->canManagePresensi())
                    <button type="button" id="lockToggleBJ" onclick="toggleViewOnly()" class="lock-toggle locked" title="Kunci untuk mencegah salah klik">
                        <svg class="unlock-icon" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        <svg class="lock-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span class="lock-label">Mode Terkunci</span>
                    </button>
                    <span class="text-[10px] text-slate-400">Buka kunci lalu klik ikon centang / M / I / A pada tabel untuk mengubah status</span>
                @endif
            </div>
            <div id="viewOnlyBannerBJ" class="view-only-banner show">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span id="viewOnlyBannerBJText">@if(auth()->user()?->canManagePresensi())Mode Lihat Saja — klik gembok untuk membuka pengubahan presensi@else Mode Lihat Saja — hanya admin/musyrif yang dapat mengubah presensi @endif</span>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in border border-slate-100">
                <div class="overflow-x-auto rekap-scroll">
                    <table class="w-full" style="min-width: {{ max(950, $numDates * 58 + 480) }}px">
                        <thead>
                            <tr>
                                <th class="px-2 py-2.5 text-center text-[11px] sticky-corner stickyleft" style="min-width: 36px; left: 0;">No</th>
                                <th class="px-2 py-2.5 text-left text-[11px] stickyleft" style="min-width: 170px; left: 36px;">Nama Santri</th>
                                <th class="px-2 py-2.5 text-center text-[11px]" style="min-width: 70px;">Persentase</th>
                                @foreach($allDates as $date)
                                    <th class="px-1 py-2.5 text-center text-[10px]" style="min-width: 58px;">
                                        {{ $date->isoFormat('ddd') }}<br><span style="font-size:9px;opacity:0.8;">{{ $date->format('d') }}</span>
                                    </th>
                                @endforeach
                                <th class="px-2 py-2.5 text-center text-[11px]" style="min-width: 50px;">Hadir</th>
                                <th class="px-2 py-2.5 text-center text-[11px]" style="min-width: 50px;">Masbuq</th>
                                <th class="px-2 py-2.5 text-center text-[11px]" style="min-width: 50px;">Izin</th>
                                <th class="px-2 py-2.5 text-center text-[11px]" style="min-width: 50px;">Alfa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rekapData as $idx => $santri)
                                @php
                                    $barWidth = $santri['persentase'];
                                    $barColor = $barWidth >= 80 ? 'from-emerald-400 to-emerald-500' : ($barWidth >= 50 ? 'from-amber-400 to-amber-500' : 'from-red-400 to-red-500');
                                @endphp
                                <tr class="hover:bg-slate-50 transition-smooth">
                                    <td class="px-2 py-2 text-center sticky-corner-left text-[11px] font-bold text-slate-500" style="background: #ffffff; min-width: 36px; left: 0;">{{ $idx + 1 }}</td>
                                    <td class="px-2 py-2 sticky-corner-left text-[11px]" style="background: #ffffff; min-width: 170px; left: 36px;">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-400 to-blue-400 flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0">{{ substr($santri['nama'], 0, 1) }}</div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate text-[12px]">{{ $santri['nama'] }}</p>
                                                <p class="text-[10px] text-slate-400 truncate">{{ $santri['kamar'] }}{{ $santri['jabatan'] ? ' · ' . $santri['jabatan'] : '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <span id="pct-{{ $santri['id'] }}" class="text-[12px] font-bold {{ $santri['persentase'] >= 80 ? 'text-emerald-600' : ($santri['persentase'] >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $santri['persentase'] }}%</span>
                                        <div class="w-full h-1 bg-slate-100 rounded-full mt-1 overflow-hidden">
                                            <div id="pctbar-{{ $santri['id'] }}" class="h-full rounded-full bg-gradient-to-r {{ $barColor }}" style="width: {{ $barWidth }}%"></div>
                                        </div>
                                    </td>
                                    @foreach($santri['hariDetail'] as $hari)
                                        @php $firstStatus = reset($hari['statuses']); @endphp
                                        <td class="px-1 py-2 text-center" data-editable title="Klik untuk mengubah presensi" onclick="openEditCell({{ $santri['id'] }}, '{{ $hari['date'] }}')">
                                            <span id="badge-{{ $santri['id'] }}-{{ $hari['date'] }}" class="editable-cell">
                                                @if($firstStatus === 'Jamaah')
                                                    <span class="status-jamaah inline-flex items-center justify-center">
                                                        <svg class="w-[11px] h-[11px]" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    </span>
                                                @elseif($firstStatus === 'Masbuq')
                                                    <span class="status-masbuq inline-block">M</span>
                                                @elseif($firstStatus === 'Izin')
                                                    <span class="status-izin inline-block">I</span>
                                                @elseif($firstStatus === 'Alfa')
                                                    <span class="status-alfa inline-block">A</span>
                                                @else
                                                    <span class="status-none inline-block">-</span>
                                                @endif
                                            </span>
                                        </td>
                                    @endforeach
                                    <td id="cnt-hadir-{{ $santri['id'] }}" class="px-2 py-2 text-center text-[11px] font-bold stickyright" style="background: #ffffff; right: 150px; color: #059669;">{{ $santri['hadir'] }}</td>
                                    <td id="cnt-masbuq-{{ $santri['id'] }}" class="px-2 py-2 text-center text-[11px] font-bold stickyright" style="background: #ffffff; right: 100px; color: #ea580c;">{{ $santri['masbuq'] }}</td>
                                    <td id="cnt-izin-{{ $santri['id'] }}" class="px-2 py-2 text-center text-[11px] font-bold stickyright" style="background: #ffffff; right: 50px; color: #2563eb;">{{ $santri['izin'] }}</td>
                                    <td id="cnt-alfa-{{ $santri['id'] }}" class="px-2 py-2 text-center text-[11px] font-bold sticky-corner-right" style="background: #ffffff; right: 0; color: #94a3b8;">{{ $santri['alfa'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-2 mt-4 mb-5 animate-slide-up">
                <span class="text-[10px] font-bold text-slate-500 uppercase">Keterangan:</span>
                <span class="status-jamaah inline-flex items-center justify-center"><svg class="w-[11px] h-[11px]" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span><span class="text-[10px] text-slate-400">= Berjamaah</span>
                <span class="status-masbuq">M</span><span class="text-[10px] text-slate-400">= Masbuq</span>
                <span class="status-izin">I</span><span class="text-[10px] text-slate-400">= Izin</span>
                <span class="status-alfa">A</span><span class="text-[10px] text-slate-400">= Alfa</span>
                <span class="status-none">-</span><span class="text-[10px] text-slate-400">= Belum Diisi</span>
            </div>

        @else
            <div class="bg-white rounded-xl shadow-sm p-8 text-center animate-fade-in border border-slate-100">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak ada data</h3>
                <p class="text-slate-500 text-sm">Belum ada data presensi untuk bulan ini.</p>
            </div>
        @endif
    </div>

    <!-- Edit Presensi Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[90]">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 animate-slide-up">
            <div class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-teal-500 to-emerald-600 rounded-t-2xl">
                <h2 class="text-white font-bold text-sm">Ubah Presensi</h2>
                <button onclick="closeEditModal()" class="text-white text-2xl leading-none hover:text-emerald-100">&times;</button>
            </div>
            <div id="editModalBody" class="p-5"></div>
        </div>
    </div>

    <!-- Confirm Save Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-[95]">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 animate-slide-up">
            <div class="p-6">
                <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-1 text-center">Simpan perubahan?</h3>
                <p class="text-[12px] text-slate-500 text-center mb-4">Pastikan perubahan berikut sudah benar:</p>
                <div id="confirmList" class="max-h-48 overflow-y-auto mb-5"></div>
                <div class="flex gap-3">
                    <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all duration-200">Batal</button>
                    <button id="confirmSaveBtn" onclick="confirmSave()" class="flex-1 px-4 py-2.5 rounded-xl bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600 transition-all duration-200 shadow-md shadow-emerald-200">Ya, Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[100]">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 animate-slide-up">
            <div class="p-6 text-center">
                <div class="mx-auto w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
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
        function toggleDropdown(id) {
            const el = document.getElementById(id);
            const menu = el.querySelector('.dropdown-menu');
            const wasHidden = menu.classList.contains('hidden');
            document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden'));
            if (wasHidden) menu.classList.remove('hidden');
        }
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[id^="settings"]')) {
                document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden'));
            }
        });
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
        function confirmLogout() { document.getElementById('logoutForm').submit(); }
    </script>

    <script>
        /* ===== EDIT PRESENSI + KUNCI + SINKRONISASI ===== */
        var isLockedBJ = true;
        var SHOW_WAKTU = @json($showWaktu);
        var TOTAL_DATES = {{ count($allDates) }};
        var NAMES = @json($namesForJs);
        var DATE_LABELS = @json($dateLabelsForJs);
        var rekapState = @json($stateForJs);

        var currentEdit = null;
        var pendingChangesFinal = [];

        var CHECK_SVG = '<svg class="w-[11px] h-[11px]" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
        var STATUS_OPTIONS = [['Jamaah', '✓ Berjamaah'], ['Masbuq', 'M Masbuq'], ['Izin', 'I Izin'], ['Alfa', 'A Alfa'], ['-', '— Kosong']];
        var STATUS_LABEL = { 'Jamaah': 'Berjamaah', 'Masbuq': 'Masbuq', 'Izin': 'Izin', 'Alfa': 'Alfa', '-': 'Kosong' };

        function badgeHtml(st) {
            if (st === 'Jamaah') return '<span class="status-jamaah inline-flex items-center justify-center">' + CHECK_SVG + '</span>';
            if (st === 'Masbuq') return '<span class="status-masbuq inline-block">M</span>';
            if (st === 'Izin') return '<span class="status-izin inline-block">I</span>';
            if (st === 'Alfa') return '<span class="status-alfa inline-block">A</span>';
            return '<span class="status-none inline-block">-</span>';
        }

        function getStatus(sid, date, waktu) {
            return (rekapState[sid] && rekapState[sid][date] && rekapState[sid][date][waktu]) || '-';
        }

        function lockBodyScrollBJ() {
            var sw = window.innerWidth - document.documentElement.clientWidth;
            document.body.style.overflow = 'hidden';
            if (sw > 0) document.body.style.paddingRight = sw + 'px';
        }
        function unlockBodyScrollBJ() {
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        function showToast(message, type) {
            var container = document.getElementById('toast-container');
            var toast = document.createElement('div');
            toast.className = 'toast ' + (type || 'success');
            toast.innerHTML = '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' + message;
            container.appendChild(toast);
            setTimeout(function() { toast.classList.add('fade-out'); }, 2800);
            setTimeout(function() { toast.remove(); }, 3100);
        }

        var canManageBJ = @json(auth()->check() && auth()->user()->canManagePresensi());

        function toggleViewOnly() {
            if (!canManageBJ) return;
            isLockedBJ = !isLockedBJ;
            var btn = document.getElementById('lockToggleBJ');
            var banner = document.getElementById('viewOnlyBannerBJ');
            var tableWrap = document.querySelector('.rekap-scroll') ? document.querySelector('.rekap-scroll').closest('.bg-white') : null;
            var unlockIcon = btn.querySelector('.unlock-icon');
            var lockIcon = btn.querySelector('.lock-icon');
            var label = btn.querySelector('.lock-label');

            if (isLockedBJ) {
                btn.classList.remove('unlocked'); btn.classList.add('locked');
                unlockIcon.style.display = 'none'; lockIcon.style.display = 'block';
                label.textContent = 'Mode Terkunci';
                banner.classList.add('show');
                if (tableWrap) tableWrap.classList.add('is-viewonly');
            } else {
                btn.classList.remove('locked'); btn.classList.add('unlocked');
                unlockIcon.style.display = 'block'; lockIcon.style.display = 'none';
                label.textContent = 'Kunci';
                banner.classList.remove('show');
                if (tableWrap) tableWrap.classList.remove('is-viewonly');
            }
        }

        function openEditCell(sid, date) {
            if (!canManageBJ) {
                showToast('Hanya admin/musyrif yang dapat mengubah presensi.', 'error');
                return;
            }
            if (isLockedBJ) {
                showToast('Buka kunci terlebih dahulu untuk mengubah presensi!', 'error');
                return;
            }
            currentEdit = { sid: sid, date: date, pending: {} };

            var html = '<p class="font-bold text-slate-800 text-sm">' + (NAMES[sid] || 'Santri') + '</p>';
            html += '<p class="text-[11px] text-slate-400 mb-4">' + (DATE_LABELS[date] || date) + '</p>';
            html += '<div class="space-y-3">';
            SHOW_WAKTU.forEach(function(w) {
                var cur = getStatus(sid, date, w);
                html += '<div><p class="text-[10px] font-bold uppercase text-slate-500 mb-1">' + w + '</p><div class="flex flex-wrap gap-1.5" id="pickrow-' + w + '">';
                STATUS_OPTIONS.forEach(function(opt) {
                    var active = opt[0] === cur ? ' active' : '';
                    html += '<button type="button" class="stbtn' + active + '" data-status="' + opt[0] + '" onclick="pickStatus(\'' + w + '\', \'' + opt[0] + '\', this)">' + opt[1] + '</button>';
                });
                html += '</div></div>';
            });
            html += '</div>';
            html += '<button type="button" onclick="saveEdits()" class="w-full mt-5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white py-2.5 rounded-xl font-bold text-sm hover:bg-emerald-600 transition-all duration-200 shadow-md shadow-emerald-200">Simpan Perubahan</button>';

            document.getElementById('editModalBody').innerHTML = html;
            var m = document.getElementById('editModal');
            m.classList.remove('hidden'); m.classList.add('flex');
            lockBodyScrollBJ();
        }

        function pickStatus(waktu, status, btn) {
            currentEdit.pending[waktu] = status;
            document.querySelectorAll('#pickrow-' + waktu + ' .stbtn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
        }

        function closeEditModal() {
            var m = document.getElementById('editModal');
            m.classList.add('hidden'); m.classList.remove('flex');
            if (document.getElementById('confirmModal').classList.contains('hidden')) unlockBodyScrollBJ();
        }

        function saveEdits() {
            var changes = [];
            SHOW_WAKTU.forEach(function(w) {
                var cur = getStatus(currentEdit.sid, currentEdit.date, w);
                var pend = currentEdit.pending[w];
                if (pend !== undefined && pend !== cur) changes.push({ w: w, from: cur, to: pend });
            });
            if (!changes.length) {
                showToast('Tidak ada perubahan status.', 'error');
                return;
            }
            pendingChangesFinal = changes;

            var listHtml = '<p class="text-[12px] font-bold text-slate-700 mb-1">' + (NAMES[currentEdit.sid] || '') + ' &middot; ' + (DATE_LABELS[currentEdit.date] || currentEdit.date) + '</p>';
            changes.forEach(function(c) {
                listHtml += '<div class="flex items-center justify-between py-1.5 border-b border-slate-100 text-[12px]"><span class="text-slate-500 font-medium">' + c.w + '</span><span class="font-bold text-slate-700">' + STATUS_LABEL[c.from] + ' <span class="text-slate-400">&rarr;</span> <span class="text-emerald-600">' + STATUS_LABEL[c.to] + '</span></span></div>';
            });
            document.getElementById('confirmList').innerHTML = listHtml;

            closeEditModal();
            var cm = document.getElementById('confirmModal');
            cm.classList.remove('hidden'); cm.classList.add('flex');
        }

        function closeConfirmModal() {
            var m = document.getElementById('confirmModal');
            m.classList.add('hidden'); m.classList.remove('flex');
            unlockBodyScrollBJ();
        }

        function renderBadge(sid, date) {
            var el = document.getElementById('badge-' + sid + '-' + date);
            if (!el) return;
            el.innerHTML = badgeHtml(getStatus(sid, date, SHOW_WAKTU[0]));
        }

        function countStatuses(sid) {
            var h = 0, m = 0, i = 0, a = 0;
            var days = rekapState[sid] || {};
            Object.keys(days).forEach(function(d) {
                Object.keys(days[d]).forEach(function(w) {
                    var st = days[d][w];
                    if (st === 'Jamaah') h++; else if (st === 'Masbuq') m++; else if (st === 'Izin') i++; else if (st === 'Alfa') a++;
                });
            });
            return { h: h, m: m, i: i, a: a };
        }

        function recountSantri(sid) {
            var c = countStatuses(sid);
            var totalJadwal = TOTAL_DATES * SHOW_WAKTU.length;
            var pct = totalJadwal > 0 ? Math.round(c.h / totalJadwal * 100) : 0;

            var set = function(id, v) { var el = document.getElementById(id); if (el) el.textContent = v; };
            set('cnt-hadir-' + sid, c.h); set('cnt-masbuq-' + sid, c.m);
            set('cnt-izin-' + sid, c.i); set('cnt-alfa-' + sid, c.a);

            var pe = document.getElementById('pct-' + sid);
            if (pe) {
                pe.textContent = pct + '%';
                pe.className = 'text-[12px] font-bold ' + (pct >= 80 ? 'text-emerald-600' : (pct >= 50 ? 'text-amber-600' : 'text-red-500'));
            }
            var be = document.getElementById('pctbar-' + sid);
            if (be) {
                be.style.width = pct + '%';
                be.className = 'h-full rounded-full bg-gradient-to-r ' + (pct >= 80 ? 'from-emerald-400 to-emerald-500' : (pct >= 50 ? 'from-amber-400 to-amber-500' : 'from-red-400 to-red-500'));
            }
            updateSummary();
        }

        function updateSummary() {
            var th = 0, tm = 0, ti = 0, ta = 0, sumPct = 0, n = 0;
            var totalJadwal = TOTAL_DATES * SHOW_WAKTU.length;
            Object.keys(rekapState).forEach(function(sid) {
                var c = countStatuses(sid);
                th += c.h; tm += c.m; ti += c.i; ta += c.a;
                sumPct += totalJadwal > 0 ? Math.round(c.h / totalJadwal * 100) : 0;
                n++;
            });
            var set = function(id, v) { var el = document.getElementById(id); if (el) el.textContent = v; };
            set('sum-hadir', th); set('sum-masbuq', tm); set('sum-izin', ti); set('sum-alfa', ta);
            set('sum-rata', (n > 0 ? Math.round(sumPct / n * 10) / 10 : 0) + '%');
        }

        function confirmSave() {
            if (!currentEdit || !pendingChangesFinal.length) return;
            var btn = document.getElementById('confirmSaveBtn');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            var sid = currentEdit.sid, date = currentEdit.date;
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var ok = 0, fail = 0;
            var chain = Promise.resolve();

            pendingChangesFinal.forEach(function(c) {
                chain = chain.then(function() {
                    var body = new URLSearchParams();
                    body.append('santri_id', sid);
                    body.append('tanggal', date);
                    body.append('waktu_sholat', c.w);
                    body.append('status', c.to === '-' ? '' : c.to);
                    return fetch("{{ route('presensi.quickStatus') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: body
                    }).then(function(r) { return r.json(); }).then(function(d) {
                        if (d.success) {
                            ok++;
                            if (!rekapState[sid]) rekapState[sid] = {};
                            if (!rekapState[sid][date]) rekapState[sid][date] = {};
                            rekapState[sid][date][c.w] = c.to;
                        } else { fail++; }
                    }).catch(function() { fail++; });
                });
            });

            chain.then(function() {
                btn.disabled = false;
                btn.textContent = 'Ya, Simpan';
                closeConfirmModal();
                renderBadge(sid, date);
                recountSantri(sid);
                currentEdit = null;
                pendingChangesFinal = [];
                if (fail === 0) showToast('Perubahan tersimpan & seluruh rekap tersinkron.', 'success');
                else showToast(fail + ' perubahan gagal disimpan. Coba lagi.', 'error');
            });
        }
    </script>
</body>
</html>
