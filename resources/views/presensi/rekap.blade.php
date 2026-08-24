<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Presensi — SIJAMAAH</title>
    <link rel="stylesheet" href="{{ asset('custom-assets/app.css') }}?v={{ filemtime(public_path('custom-assets/app.css')) }}">
    <style>
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes countUp { from { opacity: 0; transform: scale(0.5); } to { opacity: 1; transform: scale(1); } }

        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .transition-smooth { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15); }
        .filter-gradient { background: #ffffff; border: 1px solid #e2e8f0; }
        .animate-fade-in-fast { animation: fadeInFast 0.15s ease-out both; }
        @keyframes fadeInFast { from { opacity: 0; transform: translateY(-6px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .alert-animate { animation: slideDown 0.4s ease-out; }

        /* Lock / View-Only Mode */
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
            border: 1px solid #fde68a; animation: fadeInFast 0.2s ease-out;
        }
        .view-only-banner.show { display: flex; }
        .view-only-banner svg { width: 16px; height: 16px; color: #d97706; flex-shrink: 0; }

        .is-viewonly .status-btn { pointer-events: none; opacity: 0.35; cursor: not-allowed; transform: none !important; }
        .is-viewonly .status-btn.active { opacity: 0.55; }
        .is-viewonly input[data-santri-id] { pointer-events: none; opacity: 0.4; cursor: not-allowed; }
        .is-viewonly .save-all-btn { pointer-events: none; opacity: 0.35; cursor: not-allowed; }
        .is-viewonly .hapus-btn { pointer-events: none; opacity: 0.35; cursor: not-allowed; }

        .hapus-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700;
            cursor: pointer; transition: all 0.3s ease; border: 1.5px solid transparent;
            user-select: none; background: #fef2f2; color: #991b1b; border-color: #fca5a5;
        }
        .hapus-btn:hover { background: #fee2e2; box-shadow: 0 0 0 2px rgba(239,68,68,0.15); }
        .hapus-btn svg { width: 14px; height: 14px; flex-shrink: 0; }

        /* Toast notification */
        #toast-container { position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 9999; pointer-events: none; }
        .toast {
            pointer-events: auto;
            display: flex; align-items: center; gap: 10px;
            padding: 14px 24px; border-radius: 12px;
            font-size: 14px; font-weight: 600; color: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.22);
            animation: toastIn 0.35s ease-out forwards;
            max-width: 420px;
        }
        .toast.success { background: #059669; }
        .toast.error   { background: #dc2626; }
        .toast.fade-out { animation: toastOut 0.3s ease-in forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0) scale(1); } to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        tbody tr { animation: slideUp 0.5s ease-out; }
        tbody tr:nth-child(1) { animation-delay: 0s; }
        tbody tr:nth-child(2) { animation-delay: 0.05s; }
        tbody tr:nth-child(3) { animation-delay: 0.1s; }
        tbody tr:nth-child(4) { animation-delay: 0.15s; }
        tbody tr:nth-child(5) { animation-delay: 0.2s; }
        tbody tr:nth-child(n+6) { animation-delay: 0.25s; }

        .status-btn {
            padding: 4px 10px; border-radius: 5px; font-weight: 600; font-size: 11px;
            cursor: pointer; transition: all 0.3s ease; border: 1.5px solid transparent; white-space: nowrap;
        }
        .status-btn.hadir { background-color: #dcfce7; color: #166534; border-color: #86efac; }
        .status-btn.hadir:hover { background-color: #86efac; transform: scale(1.05); }
        .status-btn.masbuq { background-color: #fff7ed; color: #c2410c; border-color: #fb923c; }
        .status-btn.masbuq:hover { background-color: #fb923c; transform: scale(1.05); }
        .status-btn.izin { background-color: #dbeafe; color: #0c4a6e; border-color: #7dd3fc; }
        .status-btn.izin:hover { background-color: #7dd3fc; transform: scale(1.05); }
        .status-btn.alfa { background-color: #e5e7eb; color: #374151; border-color: #d1d5db; }
        .status-btn.alfa:hover { background-color: #d1d5db; transform: scale(1.05); }
        .status-btn.active { border-color: currentColor; box-shadow: inset 0 0 0 2px currentColor; font-weight: bold; }

        .presensi-scroll {
            max-height: 520px; overflow: auto; scrollbar-width: thin;
            scrollbar-color: #10b981 #e2e8f0;
        }
        .presensi-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .presensi-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 9999px; }
        .presensi-scroll::-webkit-scrollbar-thumb { background: #10b981; border-radius: 9999px; }
        .presensi-scroll thead th {
            position: sticky; top: 0; z-index: 10;
            background: #10b981; color: #ffffff;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.12);
        }
        .presensi-scroll thead tr { background: transparent; }

        .th-badge {
            display: inline-block;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 6px;
            padding: 3px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        /* ===== PRINT STYLES ===== */
        .print-only { display: none !important; }

        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
            body { background: #fff !important; margin: 0 !important; padding: 0 !important; font-size: 11pt; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }

            .print-page {
                display: block !important;
                width: 100%;
                padding: 15mm 12mm;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                color: #000;
            }
            .print-page .print-header {
                text-align: center;
                border-bottom: 2.5px solid #000;
                padding-bottom: 8px;
                margin-bottom: 14px;
            }
            .print-page .print-header h1 {
                font-size: 16pt;
                font-weight: 800;
                margin: 0 0 2px 0;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }
            .print-page .print-header h2 {
                font-size: 12pt;
                font-weight: 600;
                margin: 0;
                color: #333;
            }
            .print-page .print-info {
                display: flex;
                justify-content: space-between;
                font-size: 10pt;
                margin-bottom: 12px;
                padding: 6px 10px;
                background: #f3f3f3;
                border: 1px solid #ccc;
            }
            .print-page .print-info span { font-weight: 600; }

            .print-page table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
                margin-bottom: 14px;
            }
            .print-page table th,
            .print-page table td {
                border: 1px solid #000;
                padding: 5px 8px;
                text-align: center;
                vertical-align: middle;
            }
            .print-page table th {
                background: #e0e0e0 !important;
                font-weight: 700;
                font-size: 10pt;
                text-transform: uppercase;
            }
            .print-page table td:first-child { text-align: center; width: 40px; }
            .print-page table td:nth-child(2) { text-align: left; font-weight: 500; }
            .print-page table td:nth-child(4),
            .print-page table td:nth-child(5) { font-weight: 700; }

            .print-summary {
                display: flex;
                gap: 10px;
                margin-bottom: 16px;
                font-size: 10pt;
            }
            .print-summary .summary-box {
                flex: 1;
                text-align: center;
                border: 1.5px solid #000;
                padding: 6px 4px;
            }
            .print-summary .summary-box .num { font-size: 18pt; font-weight: 800; display: block; }
            .print-summary .summary-box .lbl { font-size: 8pt; text-transform: uppercase; font-weight: 600; color: #333; }

            .print-footer {
                margin-top: 20px;
                display: flex;
                justify-content: space-between;
                font-size: 9pt;
                page-break-inside: avoid;
            }
            .print-footer .sign-box { text-align: center; width: 140px; }
            .print-footer .sign-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 4px; font-weight: 600; }
        }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    <div id="toast-container"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 no-print">
        <!-- Header -->
        <div class="mb-5 animate-slide-in relative z-30">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-white shadow-lg flex items-center justify-center overflow-hidden border-[3px] border-white ring-2 ring-emerald-200">
                        <img src="{{ asset('images/image.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                            Rekap Presensi
                        </h1>
                        <p class="text-slate-500 text-xs">Lihat rekap data kehadiran sholat berjamaah santri</p>
                    </div>
                </div>
                <div class="nav-actions flex flex-wrap gap-2 items-center">
                    <a href="{{ route('presensi.index') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Dashboard</a>
                    <a href="{{ route('presensi.rankingBerjamaah') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Ranking Berjamaah</a>
                    <a href="{{ route('presensi.rekapBerjamaah') }}" class="bg-teal-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Rekap Berjamaah</a>
                    <a href="{{ route('presensi.rankingAlfa') }}" class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Ranking Alfa</a>
                    <a href="{{ route('santri.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Kelola Santri</a>
                    <!-- Settings Dropdown -->
                    <div class="relative" id="settingsRekap">
                        <button onclick="toggleDropdown('settingsRekap')" class="bg-slate-600 text-white px-3 py-2 rounded-lg font-semibold hover:bg-slate-700 shadow text-xs transition-smooth flex items-center gap-1.5">
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

        @if(session('success'))
            <div class="alert-animate mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter Section -->
        <form method="GET" action="{{ route('presensi.rekap') }}" class="filter-gradient rounded-xl p-3 mb-5 animate-fade-in shadow-sm">
            <div class="flex flex-wrap items-end gap-2.5">
                <div class="animate-slide-in">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                </div>
                <div class="animate-slide-in" style="animation-delay: 0.05s">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Waktu Sholat</label>
                    <select name="waktu_sholat" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                        @foreach(['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] as $w)
                            <option value="{{ $w }}" {{ $waktu == $w ? 'selected' : '' }}>{{ $w }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="animate-slide-in" style="animation-delay: 0.1s">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Asrama / Rayon</label>
                    <select name="kamar_id" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                        <option value="all" {{ $kamarId == 'all' ? 'selected' : '' }}>Semua Rayon</option>
                        @foreach($daftarKamar as $kamar)
                            <option value="{{ $kamar->id }}" {{ $kamarId == $kamar->id ? 'selected' : '' }}>{{ $kamar->nama_kamar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ml-auto animate-slide-in flex gap-2" style="animation-delay: 0.15s">
                    <button type="submit" class="bg-emerald-500 text-white py-1.5 px-5 rounded-md font-semibold hover:bg-emerald-600 shadow transition-smooth text-[13px] h-[34px] box-border">Tampilkan</button>
                    @if(count($santris) > 0)
                    <div class="relative" id="cetakDropdown">
                        <button type="button" onclick="toggleCetakDropdown()" class="bg-blue-500 text-white py-1.5 px-4 rounded-md font-semibold hover:bg-blue-600 shadow transition-smooth text-[13px] h-[34px] box-border flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Cetak Rekap
                            <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="cetakMenu" class="hidden absolute right-0 mt-1.5 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-50 animate-fade-in-fast">
                            <a href="javascript:void(0)" onclick="cetakRekap('mingguan')" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <div><p class="font-semibold">Mingguan</p><p class="text-[10px] text-slate-400">1 minggu terakhir</p></div>
                            </a>
                            <a href="javascript:void(0)" onclick="cetakRekap('bulanan')" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <div><p class="font-semibold">Bulanan</p><p class="text-[10px] text-slate-400">1 bulan penuh</p></div>
                            </a>
                            <a href="javascript:void(0)" onclick="cetakRekap('tahunan')" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <div><p class="font-semibold">Tahunan</p><p class="text-[10px] text-slate-400">1 tahun penuh</p></div>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </form>

        <!-- Legend/Info + Simpan Button -->
        <div class="flex flex-wrap items-center gap-2 mb-5 animate-slide-up">
            <div class="flex items-center gap-1.5 bg-white rounded-lg px-3 py-1.5 shadow-sm border border-slate-100">
                <span class="font-bold text-slate-900 text-[11px]">Hadir</span>
                <span class="text-[9px] text-slate-400">Jamaah lengkap</span>
            </div>
            <div class="flex items-center gap-1.5 bg-white rounded-lg px-3 py-1.5 shadow-sm border border-slate-100">
                <span class="font-bold text-slate-900 text-[11px]">Masbuq</span>
                <span class="text-[9px] text-slate-400">Terlambat</span>
            </div>
            <div class="flex items-center gap-1.5 bg-white rounded-lg px-3 py-1.5 shadow-sm border border-slate-100">
                <span class="font-bold text-slate-900 text-[11px]">Izin</span>
                <span class="text-[9px] text-slate-400">Ada keperluan</span>
            </div>
            <div class="flex items-center gap-1.5 bg-white rounded-lg px-3 py-1.5 shadow-sm border border-slate-100">
                <span class="font-bold text-slate-900 text-[11px]">Alfa</span>
                <span class="text-[9px] text-slate-400">Tanpa keterangan</span>
            </div>
            @if(count($santris) > 0)
            <div class="ml-auto flex items-center gap-2">
                @if(auth()->user()?->canManagePresensi())
                    <button type="button" onclick="confirmHapusPresensi()" class="hapus-btn" title="Hapus semua data presensi periode ini">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus Presensi
                    </button>
                    <button type="button" id="lockToggle" onclick="toggleViewOnly()" class="lock-toggle locked" title="Kunci untuk mode lihat saja">
                        <svg class="unlock-icon" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        <svg class="lock-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span class="lock-label">Mode Terkunci</span>
                    </button>
                    <button onclick="saveAll()" class="save-all-btn bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-5 py-1.5 rounded-lg font-bold text-[12px] hover-lift shadow-md transform transition-smooth flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Semua Presensi
                    </button>
                @endif
            </div>
            @endif
        </div>

        <!-- View-Only Banner -->
        <div id="viewOnlyBanner" class="view-only-banner no-print">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span id="viewOnlyBannerText">Mode Lihat Saja — Klik gembok untuk mengubah presensi</span>
        </div>

        <!-- Santri Table -->
        @if(count($santris) > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in border border-slate-100">
                <div class="overflow-x-auto presensi-scroll">
                    <table class="w-full min-w-[750px]">
                        <thead>
                            <tr class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
                                <th class="px-2 py-2.5 text-left text-[13px] font-bold w-[28%]"><span class="th-badge">Nama Santri</span></th>
                                <th class="px-2 py-2.5 text-center text-[13px] font-bold w-[14%]"><span class="th-badge">Rating Umum</span></th>
                                <th class="px-2 py-2.5 text-center w-[16%]"><span class="th-badge">Rating Berjamaah &middot; {{ $waktu }}</span></th>
                                <th class="px-2 py-2.5 text-center w-[18%]"><span class="th-badge">Status</span></th>
                                <th class="px-2 py-2.5 text-center w-[24%]"><span class="th-badge">Catatan</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($santris as $santri)
                                @php
                                    $currentStatus = $santri->presensis->first()?->status;
                                    $catatan = $santri->presensis->first()?->catatan ?? '';
                                @endphp
                                <tr class="hover:bg-slate-50 transition-smooth">
                                    <td class="px-3 py-2.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-blue-400 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ substr($santri->nama, 0, 1) }}</div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate text-[13px]">{{ $santri->nama }}</p>
                                                @if($santri->jabatan)
                                                    <p class="text-[11px] text-emerald-600 font-medium truncate">{{ $santri->jabatan }}</p>
                                                @else
                                                    <p class="text-[11px] text-slate-400 truncate">{{ $santri->nis }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        @php
                                            $rating = $santri->rating;
                                            $stars = round($rating / 20);
                                            $ratingUmumDetail = $santri->getRatingDetail($periodRating, $tanggal);
                                        @endphp
                                        <div class="flex items-center justify-center gap-0.5">
                                            @for($i = 0; $i < 5; $i++)
                                                <svg class="w-3.5 h-3.5 {{ $i < $stars ? 'text-yellow-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" /></svg>
                                            @endfor
                                            <span class="text-[11px] font-bold text-slate-600 ml-0.5">{{ $rating }}%</span>
                                            <button type="button" onclick="showRatingDetail('{{ addslashes($santri->nama) }}', 'Semua Waktu', {{ json_encode($ratingUmumDetail) }})" class="ml-1 w-4 h-4 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200 transition-smooth flex-shrink-0" title="Lihat rincian">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2.5 text-center">
                                        @php
                                            $ratingWaktu = $santri->ratingByWaktu ?? 0;
                                            $colorWaktu = $ratingWaktu >= 80 ? 'emerald' : ($ratingWaktu >= 50 ? 'amber' : 'red');
                                            $ratingDetail = $santri->getRatingDetailByWaktu($waktu, $periodRating, $tanggal);
                                        @endphp
                                        <div class="flex items-center justify-center gap-0.5">
                                            @for($i = 0; $i < 5; $i++)
                                                <svg class="w-3.5 h-3.5 {{ $i < round($ratingWaktu / 20) ? 'text-yellow-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" /></svg>
                                            @endfor
                                            <span class="text-[11px] font-bold text-{{ $colorWaktu }}-600 ml-0.5">{{ $ratingWaktu }}%</span>
                                            <button type="button" onclick="showRatingDetail('{{ addslashes($santri->nama) }}', '{{ $waktu }}', {{ json_encode($ratingDetail) }})" class="ml-1 w-4 h-4 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200 transition-smooth flex-shrink-0" title="Lihat rincian">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2.5">
                                        <div class="flex gap-1.5 justify-center flex-wrap" data-santri-id="{{ $santri->id }}">
                                            <button type="button" class="status-btn hadir {{ $currentStatus === 'Jamaah' ? 'active' : '' }}" onclick="updateStatus({{ $santri->id }}, 'Jamaah', this)">Hadir</button>
                                            <button type="button" class="status-btn masbuq {{ $currentStatus === 'Masbuq' ? 'active' : '' }}" onclick="updateStatus({{ $santri->id }}, 'Masbuq', this)">Masbuq</button>
                                            <button type="button" class="status-btn izin {{ $currentStatus === 'Izin' ? 'active' : '' }}" onclick="updateStatus({{ $santri->id }}, 'Izin', this)">Izin</button>
                                            <button type="button" class="status-btn alfa {{ $currentStatus === 'Alfa' ? 'active' : '' }}" onclick="updateStatus({{ $santri->id }}, 'Alfa', this)">Alfa</button>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 align-top text-center">
                                        <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 shadow-inner">
                                            <input type="text" value="{{ $catatan }}" placeholder="Catatan..." data-santri-id="{{ $santri->id }}" onchange="saveCatatan(this)" class="w-full bg-transparent border-0 outline-none text-[13px] text-slate-700 placeholder:text-slate-400 text-center">
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @else
            <div class="bg-white rounded-xl shadow-sm p-8 text-center animate-fade-in border border-slate-100">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak ada santri</h3>
                <p class="text-slate-500 text-sm mb-3">Silakan pilih kamar atau tambahkan santri terlebih dahulu.</p>
                @if(auth()->user()?->canManagePresensi())
                    <a href="{{ route('santri.create') }}" class="inline-block bg-emerald-500 text-white px-5 py-2 rounded-lg font-semibold text-sm hover:bg-emerald-600 transition-smooth shadow">Tambah Santri</a>
                @endif
            </div>
        @endif
    </div>

    <!-- ===== PRINT-ONLY RECAP TEMPLATE ===== -->
    <div class="print-only print-page">
        <div class="print-header">
            <h1>Rekap Presensi Sholat Berjamaah</h1>
            <h2>Pesantren SIJAMAAH</h2>
        </div>
        <div class="print-info">
            <div>Tanggal: <span>{{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM YYYY') }}</span></div>
            <div>Waktu Sholat: <span>{{ $waktu }}</span></div>
            <div>Rayon: <span>{{ $kamarId === 'all' || !$kamarId ? 'Semua Rayon' : $daftarKamar->firstWhere('id', $kamarId)->nama_kamar ?? '-' }}</span></div>
        </div>

        @if(count($santris) > 0)
            @php
                $countHadir = $santris->filter(fn($s) => $s->presensis->first()?->status === 'Jamaah')->count();
                $countMasbuq = $santris->filter(fn($s) => $s->presensis->first()?->status === 'Masbuq')->count();
                $countIzin = $santris->filter(fn($s) => $s->presensis->first()?->status === 'Izin')->count();
                $countAlfa = $santris->filter(fn($s) => $s->presensis->first()?->status === 'Alfa')->count();
                $countBelum = $santris->filter(fn($s) => !$s->presensis->first()?->status)->count();
            @endphp
            <div class="print-summary">
                <div class="summary-box"><span class="num">{{ $countHadir }}</span><span class="lbl">Hadir</span></div>
                <div class="summary-box"><span class="num">{{ $countMasbuq }}</span><span class="lbl">Masbuq</span></div>
                <div class="summary-box"><span class="num">{{ $countIzin }}</span><span class="lbl">Izin</span></div>
                <div class="summary-box"><span class="num">{{ $countAlfa }}</span><span class="lbl">Alfa</span></div>
                <div class="summary-box"><span class="num">{{ $countBelum }}</span><span class="lbl">Belum Diisi</span></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:36px">No</th>
                        <th style="text-align:left">Nama Santri</th>
                        <th style="width:100px">Status</th>
                        <th style="width:160px">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($santris as $i => $santri)
                        @php
                            $status = $santri->presensis->first()?->status ?? '-';
                            $catatan = $santri->presensis->first()?->catatan ?? '-';
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $santri->nama }}{{ $santri->jabatan ? ' (' . $santri->jabatan . ')' : '' }}</td>
                            <td>{{ $status }}</td>
                            <td>{{ $catatan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align:right; font-size:9pt; margin-bottom:8px; color:#555;">
                Total: <strong>{{ $santris->count() }}</strong> santri &mdash; Dicetak: <strong>{{ \Carbon\Carbon::now()->tz('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }} WIB</strong>
            </div>
        @else
            <p style="text-align:center; padding:20px; font-size:11pt;">Tidak ada data presensi untuk ditampilkan.</p>
        @endif

        <div class="print-footer">
            <div class="sign-box">
                <div class="sign-line">Mengetahui,<br>Kepala Pesantren</div>
            </div>
            <div class="sign-box">
                <div class="sign-line">Petugas Presensi</div>
            </div>
        </div>
    </div>

    <!-- Universal Modal -->
    <div id="universalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 animate-fade-in no-print">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto animate-slide-up">
            <div class="sticky top-0 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                <h2 class="text-lg font-bold" id="modalTitle">Detail</h2>
                <button onclick="closeModal()" class="text-2xl hover:text-emerald-100 transition-smooth">&times;</button>
            </div>
            <div id="modalContent" class="p-6"></div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[100] no-print">
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

    <!-- Hapus Presensi Modal -->
    <div id="hapusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[100] no-print">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 animate-slide-up">
            <div id="hapusModalContent"></div>
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

        const tanggal = "{{ $tanggal }}";
        const waktuSholat = "{{ $waktu }}";

        function updateStatus(santriId, status, button) {
            const container = button.closest('[data-santri-id]');
            container.querySelectorAll('.status-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            fetch("{{ route('presensi.quickStatus') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    santri_id: santriId,
                    tanggal: tanggal,
                    waktu_sholat: waktuSholat,
                    status: status
                })
            }).catch(error => console.error('Error:', error));
        }

        function saveCatatan(input) {
            const santriId = input.getAttribute('data-santri-id');
            const catatan = input.value;
            const status = getCurrentStatus(santriId);

            if (!status) {
                alert('Pilih status kehadiran terlebih dahulu sebelum menambah catatan!');
                return;
            }

            fetch("{{ route('presensi.quickStatus') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    santri_id: santriId,
                    tanggal: tanggal,
                    waktu_sholat: waktuSholat,
                    status: status,
                    catatan: catatan
                })
            }).catch(error => console.error('Error:', error));
        }

        function getCurrentStatus(santriId) {
            const container = document.querySelector(`[data-santri-id="${santriId}"]`);
            const activeBtn = container?.querySelector('.status-btn.active');
            if (!activeBtn) return null;
            if (activeBtn.classList.contains('hadir')) return 'Jamaah';
            if (activeBtn.classList.contains('masbuq')) return 'Masbuq';
            if (activeBtn.classList.contains('izin')) return 'Izin';
            if (activeBtn.classList.contains('alfa')) return 'Alfa';
            return null;
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

        function saveAll() {
            const tanggalVal = tanggal;
            const waktuVal = waktuSholat;

            let hasStatus = false;
            let body = new URLSearchParams();
            body.append('tanggal', tanggalVal);
            body.append('waktu_sholat', waktuVal);

            document.querySelectorAll('tbody tr').forEach(function(row) {
                const santriId = row.querySelector('[data-santri-id]')?.getAttribute('data-santri-id');
                if (santriId) {
                    const status = getCurrentStatus(santriId);
                    if (status) {
                        hasStatus = true;
                        const catatan = row.querySelector('input[data-santri-id]')?.value || '';
                        body.append('statuses[' + santriId + ']', status);
                        body.append('catatans[' + santriId + ']', catatan);
                    }
                }
            });

            if (!hasStatus) {
                showToast('Silakan pilih status kehadiran terlebih dahulu!', 'error');
                return;
            }

            fetch("{{ route('presensi.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: body.toString()
            }).then(function(response) {
                if (response.ok) {
                    showToast('Presensi berhasil disimpan!', 'success');
                } else {
                    showToast('Gagal menyimpan data. Coba lagi.', 'error');
                }
            }).catch(function() {
                showToast('Gagal menyimpan data. Coba lagi.', 'error');
            });
        }

        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = '{{ csrf_token() }}';
            document.head.appendChild(meta);
        }

        function lockBodyScroll() {
            var sw = window.innerWidth - document.documentElement.clientWidth;
            document.body.style.overflow = 'hidden';
            if (sw > 0) document.body.style.paddingRight = sw + 'px';
        }
        function unlockBodyScroll() {
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        function openModal(title, content) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('universalModal').classList.remove('hidden');
            lockBodyScroll();
        }

        function closeModal() {
            var m = document.getElementById('universalModal');
            m.style.transition = 'opacity 0.3s ease';
            m.style.opacity = '0';
            setTimeout(function() { m.classList.add('hidden'); m.style.opacity = ''; m.style.transition = ''; unlockBodyScroll(); }, 300);
        }

        function showRatingDetail(nama, waktu, detail) {
            const statusIcons = {
                'Jamaah': '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-600"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>',
                'Masbuq': '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-orange-100 text-orange-600"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 11.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3A1 1 0 0011 11.586V7z" clip-rule="evenodd"/></svg></span>',
                'Izin': '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-600"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg></span>',
                'Alfa': '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 text-red-500"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></span>'
            };

            let rows = '';
            if (detail.length === 0) {
                rows = '<p class="text-slate-400 text-center py-4 text-sm">Belum ada data presensi</p>';
            } else {
                rows = detail.map((d, i) => `
                    <div class="flex items-center justify-between py-1.5 ${i < detail.length - 1 ? 'border-b border-slate-100' : ''}">
                        <div class="flex items-center gap-2">
                            ${statusIcons[d.status] || ''}
                            <span class="text-[13px] font-medium text-slate-700">${d.status}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            ${d.waktu ? `<span class="text-[11px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">${d.waktu}</span>` : ''}
                            <span class="text-[12px] text-slate-500">${d.tanggal}</span>
                        </div>
                    </div>
                `).join('');
            }

            const hadir = detail.filter(d => d.status === 'Jamaah').length;
            const masbuq = detail.filter(d => d.status === 'Masbuq').length;
            const izin = detail.filter(d => d.status === 'Izin').length;
            const alfa = detail.filter(d => d.status === 'Alfa').length;

            const content = `
                <div class="space-y-4">
                    <div class="flex items-center gap-4 text-center">
                        <div class="flex-1 bg-emerald-50 rounded-lg p-2"><p class="text-lg font-bold text-emerald-600">${hadir}</p><p class="text-[10px] text-slate-500">Hadir</p></div>
                        <div class="flex-1 bg-orange-50 rounded-lg p-2"><p class="text-lg font-bold text-orange-600">${masbuq}</p><p class="text-[10px] text-slate-500">Masbuq</p></div>
                        <div class="flex-1 bg-blue-50 rounded-lg p-2"><p class="text-lg font-bold text-blue-600">${izin}</p><p class="text-[10px] text-slate-500">Izin</p></div>
                        <div class="flex-1 bg-red-50 rounded-lg p-2"><p class="text-lg font-bold text-red-500">${alfa}</p><p class="text-[10px] text-slate-500">Alfa</p></div>
                    </div>
                    <div class="max-h-64 overflow-y-auto pr-1">${rows}</div>
                </div>
            `;
            openModal('Rincian ' + nama + ' — ' + waktu, content);
        }
    </script>

    <script>
        function toggleCetakDropdown() {
            var menu = document.getElementById('cetakMenu');
            var wasHidden = menu.classList.contains('hidden');
            menu.classList.toggle('hidden');
            if (wasHidden) {
                setTimeout(function() { document.addEventListener('click', closeCetakMenu); }, 0);
            }
        }
        function closeCetakMenu(e) {
            var dd = document.getElementById('cetakDropdown');
            if (!dd.contains(e.target)) {
                document.getElementById('cetakMenu').classList.add('hidden');
                document.removeEventListener('click', closeCetakMenu);
            }
        }
        function cetakRekap(periode) {
            var kamarId = '{{ $kamarId }}';
            var tanggal = '{{ $tanggal }}';
            var waktu = '{{ $waktu }}';
            var url = '{{ route("presensi.rekap.cetak") }}?periode=' + periode + '&tanggal=' + tanggal + '&kamar_id=' + kamarId + '&filter_waktu=all';
            window.location.href = url;
            document.getElementById('cetakMenu').classList.add('hidden');
        }
    </script>

    <script>
        function showLogoutModal() {
            var modal = document.getElementById('logoutModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            lockBodyScroll();
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
                unlockBodyScroll();
            }, 300);
        }
        function confirmLogout() {
            document.getElementById('logoutForm').submit();
        }
        function closeHapusModal() {
            var modal = document.getElementById('hapusModal');
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '0';
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.opacity = '';
                modal.style.transition = '';
                unlockBodyScroll();
            }, 300);
        }
    </script>
    <script>
    /* ===== VIEW-ONLY / LOCK MODE ===== */
    var isLocked = true;

    function confirmHapusPresensi() {
        if (isLocked) {
            showToast('Buka kunci terlebih dahulu sebelum menghapus presensi!', 'error');
            return;
        }
        var waktu = '{{ $waktu }}';
        var tanggalFormatted = '{{ \Carbon\Carbon::parse($tanggal)->translatedFormat("d M Y") }}';
        var kamarLabel = '{{ $kamarId === "all" || !$kamarId ? "Semua Rayon" : $daftarKamar->firstWhere("id", $kamarId)->nama_kamar ?? "-" }}';
        var content = `
            <div class="p-6 text-center">
                <div class="mx-auto w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">Hapus semua presensi?</h3>
                <p class="text-sm text-slate-500 mb-6">${tanggalFormatted} &middot; ${waktu} &middot; ${kamarLabel}</p>
                <div class="flex gap-3">
                    <button onclick="closeHapusModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all duration-200">Batal</button>
                    <button onclick="executeHapusPresensi()" class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition-all duration-200 shadow-md shadow-red-200">Ya, Hapus</button>
                </div>
            </div>
        `;
        document.getElementById('hapusModalContent').innerHTML = content;
        var modal = document.getElementById('hapusModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        lockBodyScroll();
    }

    function executeHapusPresensi() {
        closeHapusModal();
        sessionStorage.setItem('_keepUnlocked', '1');
        var params = new URLSearchParams();
        params.append('tanggal', '{{ $tanggal }}');
        params.append('waktu_sholat', '{{ $waktu }}');
        params.append('kamar_id', '{{ $kamarId }}');

        fetch("{{ route('presensi.hapus') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: params.toString()
        }).then(function(res) { return res.json(); }).then(function(data) {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(function() { window.location.reload(); }, 1000);
            } else {
                showToast(data.message || 'Gagal menghapus data.', 'error');
            }
        }).catch(function() {
            showToast('Gagal menghapus data. Coba lagi.', 'error');
        });
    }

    var canManageRekap = @json(auth()->check() && auth()->user()->canManagePresensi());

    function toggleViewOnly() {
        if (!canManageRekap) return;
        isLocked = !isLocked;
        var btn = document.getElementById('lockToggle');
        var banner = document.getElementById('viewOnlyBanner');
        var tableWrap = document.querySelector('.presensi-scroll')?.closest('.bg-white');
        var unlockIcon = btn.querySelector('.unlock-icon');
        var lockIcon = btn.querySelector('.lock-icon');
        var label = btn.querySelector('.lock-label');

        if (isLocked) {
            btn.classList.remove('unlocked');
            btn.classList.add('locked');
            unlockIcon.style.display = 'none';
            lockIcon.style.display = 'block';
            label.textContent = 'Mode Terkunci';
            banner.classList.add('show');
            if (tableWrap) tableWrap.classList.add('is-viewonly');
        } else {
            btn.classList.remove('locked');
            btn.classList.add('unlocked');
            unlockIcon.style.display = 'block';
            lockIcon.style.display = 'none';
            label.textContent = 'Kunci';
            banner.classList.remove('show');
            if (tableWrap) tableWrap.classList.remove('is-viewonly');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var banner = document.getElementById('viewOnlyBanner');
        var tableWrap = document.querySelector('.presensi-scroll')?.closest('.bg-white');
        if (!canManageRekap) {
            if (document.getElementById('viewOnlyBannerText')) {
                document.getElementById('viewOnlyBannerText').textContent = 'Mode Lihat Saja — hanya admin/musyrif yang dapat mengubah presensi';
            }
            if (banner) banner.classList.add('show');
            if (tableWrap) tableWrap.classList.add('is-viewonly');
            return;
        }
        if (sessionStorage.getItem('_keepUnlocked')) {
            sessionStorage.removeItem('_keepUnlocked');
            return;
        }
        if (banner) banner.classList.add('show');
        if (tableWrap) tableWrap.classList.add('is-viewonly');
    });
    </script>
</body>
</html>
