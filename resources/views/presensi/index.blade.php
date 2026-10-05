<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Presensi Sholat Santri</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        @keyframes barGrow { from { transform: scaleY(0); } to { transform: scaleY(1); } }
        @keyframes barGrowH { from { transform: scaleX(0); } to { transform: scaleX(1); } }
        @keyframes countUp { from { opacity: 0; transform: scale(0.5); } to { opacity: 1; transform: scale(1); } }

        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .transition-smooth { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15); }
        .card-hover { transition-smooth; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }

        .rating-scroll::-webkit-scrollbar { width: 8px; }
        .rating-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .rating-scroll::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #f59e0b, #d97706); border-radius: 10px; border: 1px solid #f1f5f9; }
        .rating-scroll::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, #d97706, #b45309); }
        .filter-gradient { background: #ffffff; border: 1px solid #e2e8f0; }
        .animate-fade-in-fast { animation: fadeInFast 0.15s ease-out both; }
        @keyframes fadeInFast { from { opacity: 0; transform: translateY(-6px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .alert-animate { animation: slideDown 0.4s ease-out; }
        /* Popup terbuka: kunci scroll tanpa mengubah lebar konten (scrollbar-gutter menyimpan ruang) */
        html.modal-open { overflow: hidden; scrollbar-gutter: stable; }

        tbody tr { animation: slideUp 0.5s ease-out; }
        tbody tr:nth-child(1) { animation-delay: 0s; }
        tbody tr:nth-child(2) { animation-delay: 0.05s; }
        tbody tr:nth-child(3) { animation-delay: 0.1s; }
        tbody tr:nth-child(4) { animation-delay: 0.15s; }
        tbody tr:nth-child(5) { animation-delay: 0.2s; }
        tbody tr:nth-child(n+6) { animation-delay: 0.25s; }

        .status-btn {
            padding: 4px 10px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1.5px solid transparent;
            white-space: nowrap;
        }
        .status-btn.hadir { background-color: #dcfce7; color: #166534; border-color: #86efac; }
        .status-btn.hadir:hover { background-color: #86efac; transform: scale(1.05); }
        .status-btn.masbuq { background-color: #fff7ed; color: #c2410c; border-color: #f97316; }
        .status-btn.masbuq:hover { background-color: #f97316; transform: scale(1.05); }
        .status-btn.izin { background-color: #dbeafe; color: #0c4a6e; border-color: #7dd3fc; }
        .status-btn.izin:hover { background-color: #7dd3fc; transform: scale(1.05); }
        .status-btn.alfa { background-color: #fee2e2; color: #991b1b; border-color: #fca5a5; }
        .status-btn.alfa:hover { background-color: #fca5a5; transform: scale(1.05); }
        .status-btn.active { border-color: currentColor; box-shadow: inset 0 0 0 2px currentColor; font-weight: bold; }

        .presensi-scroll {
            max-height: 520px;
            overflow: auto;
            scrollbar-width: thin;
            scrollbar-color: #10b981 #e2e8f0;
        }
        .presensi-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .presensi-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 9999px; }
        .presensi-scroll::-webkit-scrollbar-thumb { background: #10b981; border-radius: 9999px; }
        .presensi-scroll thead th {
            position: sticky; top: 0; z-index: 10;
            background: #10b981;
            color: #ffffff; box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.12);
        }
        .presensi-scroll thead tr { background: transparent; }

        .bar-animated { transform-origin: bottom; animation: barGrow 0.8s ease-out forwards; }
        .chart-clickable { cursor: pointer; transition: all 0.2s ease; }
        .chart-clickable:hover { filter: brightness(1.1); transform: scale(1.02); }
        svg .chart-clickable { transform-box: fill-box; transform-origin: center; }

        .filter-tab {
            padding: 5px 14px; border-radius: 6px; font-size: 11px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; border: 1.5px solid transparent;
        }
        .filter-tab.active { background: #10b981; color: white; border-color: #059669; }
        .filter-tab:not(.active) { background: #f1f5f9; color: #64748b; }
        .filter-tab:not(.active):hover { background: #e2e8f0; color: #334155; }

        .bar-animated-h {
            transform-origin: left;
            animation: barGrowH 0.8s ease-out forwards;
        }
        .chart-val { font-size: 12px; font-weight: 700; font-family: system-ui, sans-serif; }
        .chart-axis-label { font-size: 12px; font-weight: 600; font-family: system-ui, sans-serif; }
        .chart-x-label { font-size: 12px; font-weight: 500; font-family: system-ui, sans-serif; }
        .scrollbar-thin { scrollbar-width: thin; scrollbar-color: #d1d5db transparent; }
        .scrollbar-thin::-webkit-scrollbar { width: 5px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 9999px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</head>
        <body class="bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50 text-slate-800 min-h-screen pb-8">
                @include('partials.app-header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">
        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Dashboard</h1>
                <p class="text-[11px] text-slate-400">Monitoring kehadiran harian santri</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-animate mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(!empty($notifData) && count($notifData) > 0)
            @php $notifTotal = count($notifData); @endphp
            <div class="mb-4 animate-fade-in">
                <button type="button" onclick="toggleDashboardNotif()" aria-expanded="false" class="w-full flex items-center gap-2.5 px-3 py-2.5 bg-white rounded-xl border border-slate-200 shadow-sm hover:bg-slate-50 transition-smooth text-left">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-[13px] font-bold text-slate-800">Peringatan Santri</span>
                        <span class="block text-[11px] text-slate-400">Klik ikon untuk melihat semua peringatan</span>
                    </span>
                    <span class="ml-auto flex items-center gap-2 flex-shrink-0">
                        <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-red-100 text-red-600 text-[11px] font-bold flex items-center justify-center">{{ $notifTotal }}</span>
                        <svg id="notifChevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div id="notifWrap" class="hidden mt-2 space-y-2">
                @foreach($notifData as $notif)
                    @php
                        $colors = match($notif['type']) {
                            'success' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'iconBg' => 'bg-emerald-100', 'iconColor' => 'text-teal-600', 'title' => 'text-emerald-700', 'text' => 'text-teal-600'],
                            'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'iconBg' => 'bg-amber-100', 'iconColor' => 'text-amber-600', 'title' => 'text-amber-700', 'text' => 'text-amber-600'],
                            'danger' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'iconBg' => 'bg-red-100', 'iconColor' => 'text-red-600', 'title' => 'text-red-700', 'text' => 'text-red-600'],
                            default => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'iconBg' => 'bg-blue-100', 'iconColor' => 'text-blue-600', 'title' => 'text-blue-700', 'text' => 'text-blue-600'],
                        };
                    @endphp
                    <div class="{{ $colors['bg'] }} border {{ $colors['border'] }} rounded-xl p-4 flex items-start gap-4 shadow-sm">
                        <div class="w-11 h-11 rounded-full {{ $colors['iconBg'] }} flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 {{ $colors['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $notif['icon'] }}"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] font-bold {{ $colors['title'] }} mb-1">{{ $notif['title'] }}</p>
                            <p class="text-[12px] {{ $colors['text'] }} leading-relaxed">{!! $notif['text'] !!}</p>
                        </div>
                    </div>
                @endforeach
                </div>
                <script>
                    function toggleDashboardNotif() {
                        var w = document.getElementById('notifWrap');
                        var c = document.getElementById('notifChevron');
                        var open = w.classList.toggle('hidden') === false;
                        if (c) c.classList.toggle('rotate-180', open);
                        var b = w.previousElementSibling;
                        if (b) b.setAttribute('aria-expanded', open ? 'true' : 'false');
                    }
                </script>
            </div>
        @endif

        @if($isPersonalMode && !$personalSantri)
            <!-- Mode Pribadi tanpa data santri -->
            <div class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-sm animate-slide-up">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    <div>
                        <p class="font-semibold">Data kehadiran belum tersedia</p>
                        <p class="mt-0.5 text-[12px] text-amber-700">Akun <strong>{{ auth()->user()->name }}</strong> tidak cocok dengan nama santri manapun. Hubungi admin untuk menghubungkan data kehadiran Anda.</p>
                    </div>
                </div>
            </div>
        @elseif(count($santris) > 0)
            @php
                $chartLabels = $chartData['presensiBulanan']['labels'];
                $chartValues = $chartData['presensiBulanan']['values'];
                $chartMax = max(100, max($chartValues ?: [100]));
                $chartTitle = $isPersonalMode ? 'Tren Kehadiran Saya' : ($chartData['presensiBulanan']['title'] ?? 'Kehadiran');
                $chartSubtitle = ($chartData['presensiBulanan']['subtitle'] ?? '')
                    . ($isPersonalMode ? ' % berjamaah (Berjamaah/total)' : '');
                $chartDetailLabels = $chartData['presensiBulanan']['detailLabels'] ?? $chartLabels;
                $chartDetailData = $chartData['presensiBulanan']['detailData'] ?? [];

                $presensiWaktuLabels = $chartData['presensiPerWaktu']['labels'];
                $presensiWaktuValues = $chartData['presensiPerWaktu']['values'];
                $presensiWaktuSubtitle = $chartData['presensiPerWaktu']['subtitle'] ?? '30 hari terakhir';
                $waktuBreakdownDetail = $chartData['waktuBreakdownDetail'] ?? [];

                // Panel "Hadir per Sholat" memakai navigasi bulan miliknya sendiri (admin/musyrif)
                $pwOffset = (int) request()->get('pw', 0);
                if (!$isPersonalMode && isset($perWaktuData)) {
                    $presensiWaktuLabels = $perWaktuData['labels'];
                    $presensiWaktuValues = $perWaktuData['values'];
                    $presensiWaktuSubtitle = $perWaktuData['subtitle'] ?? ($pwMonth->translatedFormat('F Y') ?? '');
                    $waktuBreakdownDetail = $perWaktuBreakdown ?? [];
                }

                $statusKehadiran = $chartData['statusKehadiran'];
                $statusOrder = ['Hadir', 'Masbuq', 'Izin', 'Alfa'];
                $statusColors = [
                    'Hadir' => '#22c55e',
                    'Masbuq' => '#f97316',
                    'Izin' => '#3b82f6',
                    'Alfa' => '#ef4444',
                ];

                $numLabels = count($chartLabels);
                $labelStep = max(1, ceil($numLabels / 25));
                $chartStartDate = $chartStart ?? ($chartData['dateRange']['start'] ?? date('Y-m-d'));
                $chartEndDate = $chartEnd ?? ($chartData['dateRange']['end'] ?? date('Y-m-d'));
                $chartStartMonth = substr($chartStartDate, 0, 7);
                $chartEndMonth = substr($chartEndDate, 0, 7);
                $chartDayDate = $chartStart ?? date('Y-m-d');

                $ratingStartDate = \Carbon\Carbon::parse($tanggal)->subDays($periodRating - 1)->format('d');
                $ratingEndDate = \Carbon\Carbon::parse($tanggal)->format('d');
                $ratingEndMonth = \Carbon\Carbon::parse($tanggal)->format('M');
            @endphp

            <!-- Grafik Dashboard -->
            <div class="mt-3 space-y-3 animate-slide-up">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if($isPersonalMode)
                        <!-- Rating Saya (Mode Pribadi) -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center shadow-md">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[13px] font-bold text-slate-800">Rating Saya</h3>
                                    <p class="text-[10px] text-slate-400">dari tanggal {{ $ratingStartDate }}-{{ $ratingEndDate }} {{ $ratingEndMonth }} {{ $personalSantri->nama }}</p>
                                </div>
                            </div>

                            <div class="text-center py-3">
                                <div class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-600">{{ $personalRating }}%</div>
                                @if($personalRank)
                                    <div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-[11px] font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        Peringkat #{{ $personalRank }} dari {{ $santriTotal }} santri
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <div class="bg-slate-50 rounded-lg p-2.5 text-center">
                                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Jadwal dinilai</div>
                                    <div class="text-lg font-bold text-slate-800">{{ $periodRating }} hari</div>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-2.5 text-center">
                                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">{{ $waktu }} terakhir</div>
                                    <div class="text-lg font-bold text-slate-800">{{ $personalRatingByWaktu }}%</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Rating Tertinggi -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[13px] font-bold text-slate-800">Rating Tertinggi</h3>
                                    <p class="text-[10px] text-slate-400">dari tanggal {{ $ratingStartDate }}-{{ $ratingEndDate }} {{ $ratingEndMonth }} {{ $santris->count() }} santri</p>
                                </div>
                            </div>

                            <div class="max-h-[300px] overflow-y-auto pl-1 pr-3 pb-3 rating-scroll" style="scrollbar-width: thin; scrollbar-color: #f59e0b #f1f5f9;">
                                <div class="space-y-1.5">
                                @foreach ($topRating->take(5) as $idx => $santri)
                                    @php
                                        $rating = (int) $santri->rating;
                                    @endphp
                                    <div class="flex items-center gap-2 cursor-pointer hover:bg-slate-50 p-1 rounded transition-smooth" onclick="showSantriRating('{{ addslashes($santri->nama) }}', {{ $rating }}, 'dari tanggal {{ $ratingStartDate }}-{{ $ratingEndDate }} {{ $ratingEndMonth }}')">
                                        <span class="w-5 text-center text-[11px] font-bold" style="color: {{ $idx < 3 ? ['#FFD700','#C0C0C0','#CD7F32'][$idx] : '#94a3b8' }}">{{ $idx + 1 }}</span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-0.5">
                                                <span class="text-[11px] font-semibold text-slate-800 truncate">{{ $santri->nama }}</span>
                                                <span class="text-[11px] font-bold text-slate-700 ml-1">{{ $rating }}%</span>
                                            </div>
                                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full bg-gradient-to-r {{ $idx < 3 ? ['from-amber-400 to-yellow-500', 'from-slate-400 to-slate-500', 'from-[#e2a04f] to-[#cd7f32]'][$idx] : 'from-slate-300 to-slate-400' }} transition-all duration-700" style="width: {{ $rating }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Status Kehadiran Donut (hanya utk admin/musyrif; utk pengasuh sudah di-cover Panel Saya) -->
                    @if(!$isPersonalMode)
                    @php
                        $allWaktu = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
                        $currentWaktuIdx = array_search($waktu, $allWaktu) ?? 0;
                        $prevWaktuIdx = ($currentWaktuIdx - 1 + count($allWaktu)) % count($allWaktu);
                        $nextWaktuIdx = ($currentWaktuIdx + 1) % count($allWaktu);
                        $prevWaktuUrl = request()->fullUrlWithQuery(['waktu_sholat' => $allWaktu[$prevWaktuIdx]]);
                        $nextWaktuUrl = request()->fullUrlWithQuery(['waktu_sholat' => $allWaktu[$nextWaktuIdx]]);
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center shadow-md">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-[13px] font-bold text-slate-800">Status Kehadiran</h3>
                                <p class="text-[10px] text-slate-400" data-waktu="{{ $waktu }}" data-tgl="{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}">{{ $waktu }} {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</p>
                            </div>
                            <div class="ml-auto flex items-center gap-1 shrink-0">
                                <button type="button" onclick="copyStatusChartToClipboard()" title="Salin daftar nama &rarr; paste di WhatsApp" class="w-7 h-7 rounded-lg flex items-center justify-center border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-violet-600 transition-smooth">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" /></svg>
                                </button>
                                <a href="{{ $prevWaktuUrl }}" title="Waktu sebelumnya" class="w-7 h-7 rounded-lg flex items-center justify-center border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-violet-600 transition-smooth">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                                <span class="text-[11px] font-bold text-violet-600 min-w-[48px] text-center">{{ $waktu }}</span>
                                <a href="{{ $nextWaktuUrl }}" title="Waktu berikutnya" class="w-7 h-7 rounded-lg flex items-center justify-center border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-violet-600 transition-smooth">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>

                        @php
                            $totalStatusCount = 0;
                            foreach ($statusOrder as $statusKey) {
                                $totalStatusCount += count($statusBreakdown[$statusKey] ?? []);
                            }
                            $r = 35; $cx = 50; $cy = 50;
                            $circumference = 2 * M_PI * $r;
                            $accumulatedOffset = 0;
                        @endphp

                        <div class="flex flex-row justify-start items-center gap-4 mt-8">
                            <div class="relative w-32 h-32 flex-shrink-0 flex items-center justify-center">
                                <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90" id="statusDonut">
                                    @if ($totalStatusCount > 0)
                                        @foreach ($statusOrder as $status)
                                            @php
                                                $cnt = count($statusBreakdown[$status] ?? []);
                                                $percentage = $cnt / $totalStatusCount;
                                                $dashLength = $percentage * $circumference;
                                                $dashGap = $circumference - $dashLength;
                                                $offset = -$accumulatedOffset;
                                                $accumulatedOffset += $dashLength;
                                            @endphp
                                            @if ($cnt > 0)
                                                 <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="transparent"
                                                        stroke="{{ $statusColors[$status] }}" stroke-width="8" stroke-linecap="butt"
                                                        stroke-dasharray="{{ sprintf('%.4f', $dashLength) }} {{ sprintf('%.4f', $dashGap) }}"
                                                        stroke-dashoffset="{{ sprintf('%.4f', $offset) }}"
                                                        class="cursor-pointer hover:opacity-80 transition-all duration-200"
                                                        onclick="showStatusDetail('{{ $status }}')">
                                                        <title>{{ $status }}: {{ $cnt }} santri ({{ round($percentage * 100) }}%)</title>
                                                    </circle>
                                            @endif
                                        @endforeach
                                    @else
                                        <circle cx="50" cy="50" r="35" fill="transparent" stroke="#e2e8f0" stroke-width="8" stroke-linecap="butt" />
                                    </svg>
                                    @endif
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                                    <span class="text-sm font-bold text-slate-800">{{ $totalStatusCount }}</span>
                                    <span class="text-[8px] text-slate-500 leading-tight">Santri<br>{{ $waktu }}</span>
                                </div>
                            </div>

                            <div class="flex-1 space-y-0.5 text-[11px] text-slate-700">
                                @foreach ($statusOrder as $status)
                                    @php
                                        $cnt = count($statusBreakdown[$status] ?? []);
                                        $pct = $totalStatusCount > 0 ? round(($cnt / $totalStatusCount) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center justify-between cursor-pointer hover:bg-slate-100 p-1 rounded transition-smooth" onclick="showStatusDetail('{{ $status }}')">
                                        <span class="flex items-center gap-1.5">
                                            <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $statusColors[$status] }}"></span>
                                            <span class="font-medium hover:font-semibold">{{ $status }}</span>
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 text-[10px]">{{ $pct }}%</span>
                                            <span class="font-semibold text-slate-900" id="count-{{ strtolower($status) }}">{{ $cnt }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Hadir per Sholat (hanya utk admin/musyrif; utk pengasuh sudah di-cover Panel Saya) -->
            @if(!$isPersonalMode)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mt-3">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[14px] font-bold text-slate-800">Hadir per Sholat</h3>
                        <p class="text-[11px] text-slate-400">{!! $presensiWaktuSubtitle !!} Klik untuk detail</p>
                    </div>

                    @if(!$isPersonalMode && isset($pwMonth))
                        @php
                            $prevUrl = request()->fullUrlWithQuery(['pw' => $pwOffset - 1]);
                            $nextUrl = request()->fullUrlWithQuery(['pw' => $pwOffset + 1]);
                            $isCurrentMonth = (\Illuminate\Support\Carbon::now()->format('Y-m') === $pwMonth->format('Y-m'));
                        @endphp
                        <div class="ml-auto flex items-center gap-1.5 shrink-0">
                            <a href="{{ $prevUrl }}" title="Bulan sebelumnya" class="w-8 h-8 rounded-lg flex items-center justify-center border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-teal-600 transition-smooth">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                            <span class="text-[11px] font-bold text-slate-700 min-w-[64px] text-center">{{ $pwMonth->translatedFormat('M Y') }}</span>
                            <a href="{{ $nextUrl }}" title="Bulan berikutnya" class="w-8 h-8 rounded-lg flex items-center justify-center border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-teal-600 transition-smooth {{ $isCurrentMonth ? 'opacity-40 pointer-events-none' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    @endif
                </div>

                @if($pwHasData ?? true)
                <div class="flex items-end gap-2 h-40">
                    @foreach ($presensiWaktuValues as $index => $value)
                        @php
                            $barHeightPx = max(6, round($value * 1.8));
                            $delay = $index * 0.1;
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end cursor-pointer group" style="height: 100%;" onclick="showWaktuDetail('{{ $presensiWaktuLabels[$index] }}')">
                            <span class="text-[12px] font-bold text-slate-700 mb-1">{{ $value }}%</span>
                            <div class="w-full rounded-t-lg bg-gradient-to-t from-blue-600 to-blue-400 bar-animated group-hover:from-indigo-600 group-hover:to-indigo-400 transition-colors"
                                 style="height: {{ $barHeightPx }}px; animation-delay: {{ $delay }}s;"></div>
                            <span class="text-[12px] text-slate-500 font-medium mt-1">{{ $presensiWaktuLabels[$index] }}</span>
                        </div>
                    @endforeach
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">Belum ada data</p>
                    <p class="text-[11px] text-slate-400">Tidak ada presensi yang diinput pada bulan ini.</p>
                </div>
                @endif
            </div>
            @endif

            @if(!$isPersonalMode)
            <!-- Tren Kehadiran 6 Bulan -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-800">Tren Kehadiran 6 Bulan</h3>
                            <p class="text-[11px] text-slate-400">Persentase berjamaah per bulan Klik titik untuk detail</p>
                        </div>
                    </div>
                    @php
                        $trenVals = collect($trenBulanan)->pluck('pct')->all();
                        $trenMax = max(100, max($trenVals ?: [100]));
                        $trenLeft = 54; $trenRight = 600; $trenTop = 30; $trenBottom = 140;
                        $trenStep = count($trenBulanan) > 1 ? ($trenRight - $trenLeft) / (count($trenBulanan) - 1) : 0;
                        $trenPoints = [];
                        foreach ($trenBulanan as $i => $t) {
                            $x = $trenLeft + ($i * $trenStep);
                            $y = $trenBottom - (($t['pct'] / $trenMax) * ($trenBottom - $trenTop));
                            $trenPoints[] = ['x' => round($x, 1), 'y' => round($y, 1), 'label' => $t['label'], 'pct' => $t['pct']];
                        }
                        $trenLinePath = implode(' ', array_map(fn ($p) => "{$p['x']},{$p['y']}", $trenPoints));
                        $trenAreaPath = $trenLinePath . " " . end($trenPoints)['x'] . ",$trenBottom $trenLeft,$trenBottom";
                    @endphp
                    <svg viewBox="0 0 620 185" class="w-full h-auto tren-chart">
                        <!-- Grid lines -->
                        <g stroke="#e2e8f0" stroke-width="0.5">
                            <line x1="{{ $trenLeft }}" y1="{{ $trenBottom }}" x2="{{ $trenRight }}" y2="{{ $trenBottom }}"/>
                            <line x1="{{ $trenLeft }}" y1="{{ round($trenBottom - (25 / $trenMax) * ($trenBottom - $trenTop), 1) }}" x2="{{ $trenRight }}" y2="{{ round($trenBottom - (25 / $trenMax) * ($trenBottom - $trenTop), 1) }}"/>
                            <line x1="{{ $trenLeft }}" y1="{{ round($trenBottom - (50 / $trenMax) * ($trenBottom - $trenTop), 1) }}" x2="{{ $trenRight }}" y2="{{ round($trenBottom - (50 / $trenMax) * ($trenBottom - $trenTop), 1) }}"/>
                            <line x1="{{ $trenLeft }}" y1="{{ round($trenBottom - (75 / $trenMax) * ($trenBottom - $trenTop), 1) }}" x2="{{ $trenRight }}" y2="{{ round($trenBottom - (75 / $trenMax) * ($trenBottom - $trenTop), 1) }}"/>
                            <line x1="{{ $trenLeft }}" y1="{{ $trenTop }}" x2="{{ $trenRight }}" y2="{{ $trenTop }}"/>
                        </g>

                        <!-- Vertical grid lines -->
                        <g stroke="#e2e8f0" stroke-width="0.5" opacity="0.5">
                            @foreach ($trenPoints as $p)
                                <line x1="{{ $p['x'] }}" y1="{{ $trenTop }}" x2="{{ $p['x'] }}" y2="{{ $trenBottom }}"/>
                            @endforeach
                        </g>

                        <!-- Y-axis labels -->
                        <g fill="#94a3b8" class="chart-axis-label">
                            <text x="36" y="{{ $trenBottom + 4 }}" text-anchor="end">0%</text>
                            <text x="36" y="{{ round($trenBottom - (25 / $trenMax) * ($trenBottom - $trenTop), 1) + 4 }}" text-anchor="end">25%</text>
                            <text x="36" y="{{ round($trenBottom - (50 / $trenMax) * ($trenBottom - $trenTop), 1) + 4 }}" text-anchor="end">50%</text>
                            <text x="36" y="{{ round($trenBottom - (75 / $trenMax) * ($trenBottom - $trenTop), 1) + 4 }}" text-anchor="end">75%</text>
                            <text x="36" y="{{ $trenTop + 4 }}" text-anchor="end">100%</text>
                        </g>

                        <!-- Area fill -->
                        <polygon points="{{ $trenAreaPath }}" fill="url(#trenAreaGradient)" opacity="0.3"/>
                        <defs>
                            <linearGradient id="trenAreaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#2563eb" stop-opacity="0.4"/>
                                <stop offset="100%" stop-color="#2563eb" stop-opacity="0.02"/>
                            </linearGradient>
                        </defs>

                        <!-- Line -->
                        <polyline points="{{ $trenLinePath }}" fill="none" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Data points -->
                        @foreach ($trenPoints as $i => $p)
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5" fill="white" stroke="#2563eb" stroke-width="2"
                                    class="chart-clickable" onclick="showTrenDetail({{ $i }})">
                                <title>{{ $p['label'] }}: {{ $p['pct'] }}% &mdash; klik untuk detail</title>
                            </circle>
                            <!-- Value label -->
                            <text x="{{ $p['x'] }}" y="{{ $p['y'] - 11 }}" text-anchor="middle" fill="#2563eb" class="chart-val">{{ $p['pct'] }}%</text>
                        @endforeach

                        <!-- X-axis labels -->
                        <g fill="#64748b" class="chart-x-label">
                            @foreach ($trenPoints as $p)
                                <text x="{{ $p['x'] }}" y="170" text-anchor="middle">{{ $p['label'] }}</text>
                            @endforeach
                        </g>
                    </svg>
                </div>
            @endif

                <!-- Kehadiran Chart + Filter Tabs -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center shadow-md">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[14px] font-bold text-slate-800">{{ $chartTitle }}</h3>
                                <p class="text-[11px] text-slate-400">{{ $chartSubtitle }} Klik untuk detail</p>
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center gap-2">
                            @php $filters = ['hari' => 'Per Hari', 'minggu' => 'Per Minggu', 'bulan' => 'Per Bulan', 'tahun' => 'Per Tahun']; @endphp
                            @foreach($filters as $key => $label)
                                <button type="button" class="filter-tab chart-filter-tab {{ $chartFilter === $key ? 'active' : '' }}" onclick="switchChartFilter('{{ $key }}', event)">{{ $label }}</button>
                            @endforeach
                            <div id="hariRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg p-2">
                                <span class="text-[11px] text-slate-500 shrink-0">Pilih minggu</span>
                                <input type="date" id="chartDayDate" value="{{ $chartDayDate }}" class="flex-1 min-w-0 sm:max-w-[170px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                                <span class="text-[10px] text-slate-400 shrink-0">Sen &ndash; Minggu</span>
                                <button type="button" onclick="applyChartFilter()" class="shrink-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-1 rounded text-[11px] font-semibold hover:opacity-90 transition">Terapkan</button>
                            </div>
                            <div id="mingguRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg p-2">
                                <span class="text-[11px] text-slate-500 shrink-0">Pilih bulan</span>
                                <input type="month" id="chartWeekMonth" value="{{ $chartEndMonth }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                                <span class="text-[10px] text-slate-400 shrink-0">per minggu</span>
                                <button type="button" onclick="applyChartFilter()" class="shrink-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-1 rounded text-[11px] font-semibold hover:opacity-90 transition">Terapkan</button>
                            </div>
                            <div id="bulanRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg p-2">
                                <input type="month" id="chartStartMonth" value="{{ $chartStartMonth }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                                <span class="text-[10px] text-slate-400 shrink-0">s/d</span>
                                <input type="month" id="chartEndMonth" value="{{ $chartEndMonth }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                                <button type="button" onclick="applyChartFilter()" class="shrink-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-1 rounded text-[11px] font-semibold hover:opacity-90 transition">Terapkan</button>
                            </div>
                            <div id="tahunRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg p-2">
                                <span class="text-[11px] text-slate-500">Menampilkan data tahun ini</span>
                                <button type="button" onclick="applyChartFilter()" class="shrink-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-1 rounded text-[11px] font-semibold hover:opacity-90 transition">Terapkan</button>
                            </div>
                        </div>
                    </div>

                    @php
                        $chartWidth = 546;
                        $chartLeft = 54;
                        $chartTop = 20;
                        $chartBottom = 140;
                        $chartHeight = $chartBottom - $chartTop;
                        $step = $numLabels > 1 ? $chartWidth / ($numLabels - 1) : 0;

                        $linePoints = [];
                        $areaPoints = [];
                        foreach ($chartValues as $index => $value) {
                            $x = $chartLeft + ($index * $step);
                            $y = $chartBottom - (($value / $chartMax) * $chartHeight);
                            $linePoints[] = "$x,$y";
                            $areaPoints[] = ['x' => $x, 'y' => $y];
                        }
                        $linePath = implode(' ', $linePoints);
                        $areaPath = $linePath . " " . ($chartLeft + ($numLabels - 1) * $step) . ",$chartBottom $chartLeft,$chartBottom";
                    @endphp

                    <svg viewBox="0 0 620 185" class="w-full h-auto line-chart" id="lineChart">
                        <!-- Horizontal grid lines -->
                        <g stroke="#e2e8f0" stroke-width="0.5">
                            <line x1="54" y1="140" x2="600" y2="140"/>
                            <line x1="54" y1="111" x2="600" y2="111"/>
                            <line x1="54" y1="82" x2="600" y2="82"/>
                            <line x1="54" y1="53" x2="600" y2="53"/>
                            <line x1="54" y1="20" x2="600" y2="20"/>
                        </g>

                        <!-- Vertical grid lines -->
                        <g stroke="#e2e8f0" stroke-width="0.5" opacity="0.5">
                            @for($i = 0; $i < $numLabels; $i += $labelStep)
                                <line x1="{{ $chartLeft + ($i * $step) }}" y1="{{ $chartTop }}" x2="{{ $chartLeft + ($i * $step) }}" y2="{{ $chartBottom }}"/>
                            @endfor
                            @if(($numLabels - 1) % $labelStep !== 0)
                                <line x1="{{ $chartLeft + (($numLabels - 1) * $step) }}" y1="{{ $chartTop }}" x2="{{ $chartLeft + (($numLabels - 1) * $step) }}" y2="{{ $chartBottom }}"/>
                            @endif
                        </g>

                        <!-- Y-axis labels -->
                        <g fill="#94a3b8" class="chart-axis-label">
                            <text x="36" y="143" text-anchor="end">0%</text>
                            <text x="36" y="114" text-anchor="end">25%</text>
                            <text x="36" y="85" text-anchor="end">50%</text>
                            <text x="36" y="56" text-anchor="end">75%</text>
                            <text x="36" y="23" text-anchor="end">100%</text>
                        </g>

                        <!-- Area fill -->
                        <polygon points="{{ $areaPath }}" fill="url(#areaGradient)" opacity="0.3"/>
                        <defs>
                            <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.4"/>
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.02"/>
                            </linearGradient>
                        </defs>

                        <!-- Line -->
                        <polyline points="{{ $linePath }}" fill="none" stroke="#10b981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- Data points -->
                        @foreach ($areaPoints as $index => $point)
                            <g class="chart-pt">
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="12" fill="transparent"
                                        class="chart-clickable" onclick="showLineChartDetail({{ $index }})">
                                    <title>{{ $chartDetailLabels[$index] ?? $chartLabels[$index] }}: {{ $chartValues[$index] }}% &mdash; klik untuk detail</title>
                                </circle>
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4.5" fill="white" stroke="#10b981" stroke-width="2"
                                        class="chart-clickable" onclick="showLineChartDetail({{ $index }})"
                                        style="animation: countUp 0.4s ease-out {{ $index * 0.08 }}s both;"></circle>
                                <!-- Value label -->
                                <text x="{{ $point['x'] }}" y="{{ max($point['y'] - 10, 14) }}" text-anchor="middle" fill="#059669" class="chart-val"
                                      style="animation: countUp 0.4s ease-out {{ $index * 0.08 + 0.1 }}s both;">{{ $chartValues[$index] }}%</text>
                            </g>
                        @endforeach

                        <!-- X-axis labels -->
                        <g fill="#64748b" class="chart-x-label">
                            @foreach ($chartLabels as $index => $label)
                                @if($index % $labelStep === 0 || $index === $numLabels - 1)
                                    <text x="{{ $chartLeft + ($index * $step) }}" y="168" text-anchor="middle">{{ $label }}</text>
                                @endif
                            @endforeach
                        </g>
                    </svg>
                </div>
            </div>

            <!-- Diagram Per Orang: Stacked Bar (hanya utk admin/musyrif; utk pengasuh sudah di-cover Panel Saya) -->
            @if(!$isPersonalMode)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mt-3">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-400 to-red-500 flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[14px] font-bold text-slate-800">{{ $perOrangData['isSelf'] ? 'Diagram Kehadiran Saya' : 'Diagram Per Orang' }}</h3>
                        <p class="text-[11px] text-slate-400">{{ $perOrangData['periodeLabel'] }} Klik bar untuk detail</p>
                    </div>
                </div>

                <!-- Filter: Tab Periode + Waktu Sholat -->
                <div class="mb-3 pt-2.5 border-t border-slate-100">
                    <div class="flex flex-wrap items-center gap-2">
                        @php $poFilters = ['minggu' => 'Per Minggu', 'bulan' => 'Per Bulan', 'tanggal' => 'Per Tanggal']; @endphp
                        @foreach($poFilters as $key => $label)
                            <button type="button" class="filter-tab perorang-filter-tab {{ $perOrangPeriode === $key ? 'active' : '' }}" onclick="switchPerOrangFilter('{{ $key }}', event)">{{ $label }}</button>
                        @endforeach
                        <select id="perOrangWaktu" class="ml-2 border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                            <option value="all" {{ $perOrangWaktu === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                            @foreach(['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] as $w)
                                <option value="{{ $w }}" {{ $perOrangWaktu === $w ? 'selected' : '' }}>{{ $w }}</option>
                            @endforeach
                        </select>
                        <select id="perOrangKeterangan" class="ml-2 border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                            <option value="all" {{ ($perOrangKeterangan ?? 'all') === 'all' ? 'selected' : '' }}>Semua Keterangan</option>
                            <option value="Jamaah" {{ ($perOrangKeterangan ?? '') === 'Jamaah' ? 'selected' : '' }}>Berjamaah</option>
                            <option value="Masbuq" {{ ($perOrangKeterangan ?? '') === 'Masbuq' ? 'selected' : '' }}>Masbuq</option>
                            <option value="Izin" {{ ($perOrangKeterangan ?? '') === 'Izin' ? 'selected' : '' }}>Izin</option>
                            <option value="Alfa" {{ ($perOrangKeterangan ?? '') === 'Alfa' ? 'selected' : '' }}>Alfa</option>
                        </select>
                        <button type="button" onclick="applyPerOrangFilter()" class="ml-1 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-1 rounded text-[11px] font-semibold hover:opacity-90 transition">Terapkan</button>
                    </div>
                    <!-- Date Range Picker -->
                    <div id="perOrangMingguRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-2 bg-slate-50 border border-slate-200 rounded-lg p-2">
                        <input type="date" id="perOrangStartDate" value="{{ $perOrangStart ?? now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d') }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                        <span class="text-[10px] text-slate-400 shrink-0">s/d</span>
                        <input type="date" id="perOrangEndDate" value="{{ $perOrangEnd ?? now()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('Y-m-d') }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                        <button type="button" onclick="applyPerOrangFilter()" class="shrink-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-1 rounded text-[11px] font-semibold hover:opacity-90 transition">Terapkan</button>
                    </div>
                    <div id="perOrangBulanRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-2 bg-slate-50 border border-slate-200 rounded-lg p-2">
                        <input type="month" id="perOrangStartMonth" value="{{ $perOrangStart ?? now()->format('Y-m') }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                        <span class="text-[10px] text-slate-400 shrink-0">s/d</span>
                        <input type="month" id="perOrangEndMonth" value="{{ $perOrangEnd ?? now()->format('Y-m') }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                        <button type="button" onclick="applyPerOrangFilter()" class="shrink-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-1 rounded text-[11px] font-semibold hover:opacity-90 transition">Terapkan</button>
                    </div>
                    <div id="perOrangTanggalRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-2 bg-slate-50 border border-slate-200 rounded-lg p-2">
                        <input type="date" id="perOrangTglStart" value="{{ $perOrangStart ?? now()->format('Y-m-d') }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                        <span class="text-[10px] text-slate-400 shrink-0">s/d</span>
                        <input type="date" id="perOrangTglEnd" value="{{ $perOrangEnd ?? now()->format('Y-m-d') }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                        <button type="button" onclick="applyPerOrangFilter()" class="shrink-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-1 rounded text-[11px] font-semibold hover:opacity-90 transition">Terapkan</button>
                    </div>
                </div>

                <!-- Legend -->
                @php $pk = $perOrangKeterangan ?? 'all'; @endphp
                <div class="flex flex-wrap items-center gap-3 mb-3 text-[11px]">
                    @if($pk === 'all' || $pk === 'Jamaah')
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-emerald-500"></span> Berjamaah</span>
                    @endif
                    @if($pk === 'all' || $pk === 'Masbuq')
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-orange-500"></span> Masbuq</span>
                    @endif
                    @if($pk === 'all' || $pk === 'Izin')
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-blue-500"></span> Izin</span>
                    @endif
                    @if($pk === 'all' || $pk === 'Alfa')
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm" style="background-color:#ef4444"></span> Alfa</span>
                    @endif
                    <span class="ml-auto text-slate-400">Total jadwal: {{ $perOrangData['totalJadwal'] }}</span>
                </div>

                <!-- Stacked Bars -->
                @php $poData = $perOrangData['data']; @endphp
                @if(count($poData) > 0)
                    <div class="space-y-2" id="perOrangBars">
                        @foreach($poData as $po)
                            @php
                                $pHadir = round($po['hadir'] / max(1, $perOrangData['totalJadwal']) * 100, 2);
                                $pMasbuq = round($po['masbuq'] / max(1, $perOrangData['totalJadwal']) * 100, 2);
                                $pIzin = round($po['izin'] / max(1, $perOrangData['totalJadwal']) * 100, 2);
                                $pAlfa = round($po['alfa'] / max(1, $perOrangData['totalJadwal']) * 100, 2);
                                $barTotal = $pHadir + $pMasbuq + $pIzin + $pAlfa;
                                if ($barTotal > 100) {
                                    $scale = 100 / $barTotal;
                                    $pHadir *= $scale;
                                    $pMasbuq *= $scale;
                                    $pIzin *= $scale;
                                    $pAlfa *= $scale;
                                }
                            @endphp
                            <div class="flex items-center gap-2 group cursor-pointer hover:bg-slate-50 p-1 rounded-lg transition-smooth"
                                 onclick="showPerOrangDetail('{{ addslashes($po['nama']) }}', {{ $po['hadir'] }}, {{ $po['masbuq'] }}, {{ $po['izin'] }}, {{ $po['alfa'] }}, {{ $perOrangData['totalJadwal'] }}, '{{ addslashes($po['kamar']) }}')">
                                <div class="w-28 sm:w-36 shrink-0 text-right">
                                    <p class="text-[11px] font-semibold text-slate-800 truncate group-hover:text-teal-600 transition-colors">{{ $po['nama'] }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $po['kamar'] }}</p>
                                </div>
                                <div class="flex-1 h-5 bg-slate-100 rounded-full overflow-hidden flex" title="J:{{ $po['hadir'] }} M:{{ $po['masbuq'] }} I:{{ $po['izin'] }} A:{{ $po['alfa'] }}">
                                    @if($po['hadir'] > 0)
                                        <div class="h-full bg-emerald-500 transition-all duration-500 @if(($perOrangKeterangan ?? 'all') !== 'all' && ($perOrangKeterangan ?? '') !== 'Jamaah') hidden @endif" style="width: {{ $pHadir }}%"></div>
                                    @endif
                                    @if($po['masbuq'] > 0)
                                        <div class="h-full transition-all duration-500 @if(($perOrangKeterangan ?? 'all') !== 'all' && ($perOrangKeterangan ?? '') !== 'Masbuq') hidden @endif" style="background-color: #f97316; width: {{ $pMasbuq }}%"></div>
                                    @endif
                                    @if($po['izin'] > 0)
                                        <div class="h-full bg-blue-500 transition-all duration-500 @if(($perOrangKeterangan ?? 'all') !== 'all' && ($perOrangKeterangan ?? '') !== 'Izin') hidden @endif" style="width: {{ $pIzin }}%"></div>
                                    @endif
                                    @if($po['alfa'] > 0)
                                        <div class="h-full transition-all duration-500 @if(($perOrangKeterangan ?? 'all') !== 'all' && ($perOrangKeterangan ?? '') !== 'Alfa') hidden @endif" style="background-color: #ef4444; width: {{ $pAlfa }}%"></div>
                                    @endif
                                </div>
                                <span class="w-10 text-right text-[11px] font-bold text-slate-700 shrink-0">{{ $po['persentase'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-slate-400 text-sm">
                        <p>Belum ada data presensi untuk periode ini.</p>
                    </div>
            @endif
            </div>
            @endif
        @else
            <div class="bg-white rounded-xl shadow-lg p-8 text-center animate-slide-up">
                <h3 class="text-xl font-bold text-slate-800 mb-1">Tidak ada santri</h3>
                <p class="text-slate-500 text-sm mb-3">Silakan pilih kamar atau tambahkan santri terlebih dahulu.</p>
                @if(auth()->user()?->canManagePresensi())
                    <a href="{{ route('santri.create') }}" class="inline-block bg-gradient-to-r from-teal-500 to-teal-600 text-white px-5 py-2 rounded-lg font-semibold hover-lift text-sm">Tambah Santri</a>
                @endif
            </div>
        @endif

        <!-- Universal Modal -->
        <div id="universalModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-[1000] animate-fade-in">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto animate-slide-up">
                <div class="sticky top-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                    <h2 class="text-lg font-bold" id="modalTitle">Detail</h2>
                    <button onclick="closeModal()" class="text-2xl hover:text-emerald-100 transition-smooth">&times;</button>
                </div>
                <div id="modalContent" class="p-6"></div>
            </div>
        </div>

        <!-- Hidden JSON Data -->
        <script type="application/json" id="statusBreakdownData">
            @php
                echo json_encode(array_map(function($status) use ($statusBreakdown) {
                    return [
                        'status' => $status,
                        'santris' => $statusBreakdown[$status] ?? []
                    ];
                }, ['Hadir', 'Masbuq', 'Izin', 'Alfa']));
            @endphp
        </script>
        <script type="application/json" id="waktuBreakdownData">
            @php echo json_encode($waktuBreakdownDetail ?? []); @endphp
        </script>
        <script type="application/json" id="lineChartData">
            @php echo json_encode($chartDetailData ?? []); @endphp
        </script>
        <script type="application/json" id="lineChartLabels">
            @php echo json_encode($chartDetailLabels ?? []); @endphp
        </script>
        <script type="application/json" id="trenDetailData">
            @php echo json_encode(collect($trenBulanan)->pluck('detail')); @endphp
        </script>
    </div>

    <div id="logoutModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[1010]">
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
        // === Settings Dropdown ===
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
        const IS_PERSONAL_MODE = @json($isPersonalMode);

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

        function saveAll() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('presensi.store') }}";

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            const inputs = [
                { name: '_token', value: csrfToken },
                { name: 'tanggal', value: tanggal },
                { name: 'waktu_sholat', value: waktuSholat }
            ];

            let hasStatus = false;
            document.querySelectorAll('tbody tr').forEach((row) => {
                const santriId = row.querySelector('[data-santri-id]')?.getAttribute('data-santri-id');
                if (santriId) {
                    const status = getCurrentStatus(santriId);
                    if (status) {
                        hasStatus = true;
                        const catatan = row.querySelector('input[data-santri-id]')?.value || '';
                        inputs.push({ name: `statuses[${santriId}]`, value: status });
                        inputs.push({ name: `catatans[${santriId}]`, value: catatan });
                    }
                }
            });

            if (!hasStatus) {
                alert('Silakan pilih status kehadiran terlebih dahulu!');
                return;
            }

            inputs.forEach(input => {
                const field = document.createElement('input');
                field.type = 'hidden';
                field.name = input.name;
                field.value = input.value;
                form.appendChild(field);
            });

            document.body.appendChild(form);
            form.submit();
        }

        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = '{{ csrf_token() }}';
            document.head.appendChild(meta);
        }

        // === MODAL FUNCTIONS ===
        function lockBodyScroll() {
            document.documentElement.classList.add('modal-open');
        }
        function unlockBodyScroll() {
            document.documentElement.classList.remove('modal-open');
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

        function getStatusColor(status) {
            const colors = { 'Hadir': '#22c55e', 'Masbuq': '#f97316', 'Izin': '#3b82f6', 'Alfa': '#ef4444' };
            return colors[status] || '#64748b';
        }

        // Format data dari /presensi/detail: { label: { nama: [tanggal, ...] } }
        function groupNamesWithDates(grouped) {
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            return Object.entries(grouped || {}).map(([nama, tanggalList]) => {
                const tglArr = (tanggalList || []).map(t => {
                    const p = String(t).split('-');
                    return { y: parseInt(p[0]), m: parseInt(p[1]), d: parseInt(p[2]) };
                }).sort((a, b) => a.y - b.y || a.m - b.m || a.d - b.d);
                const chips = tglArr.map(t =>
                    `<span style="background:#f1f5f9;color:#475569;border-radius:6px;padding:1px 6px;font-size:10px;white-space:nowrap">${t.d}&nbsp;${months[t.m - 1]}</span>`
                ).join('');
                return `<div style="padding:3px 0;line-height:1.6"><div style="font-weight:500;color:#0f172a;font-size:12px">${nama}</div><div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:3px">${chips}</div></div>`;
            }).join('');
        }

        const STATUS_BOX = {
            'Hadir':   { text: '#065f46', countBg: '#d1fae5', countText: '#059669' },
            'Masbuq':  { text: '#92400e', countBg: '#fde68a', countText: '#d97706' },
            'Izin':    { text: '#1e40af', countBg: '#bfdbfe', countText: '#2563eb' },
            'Alfa':    { text: '#991b1b', countBg: '#fee2e2', countText: '#dc2626' },
        };

        // Ambil daftar nama + tanggal dari server saat modal dibuka.
        // Halaman utama tidak lagi membawa data ini (Hundreds of thousands of rows).
        async function fetchDetailNames(start, end, waktu) {
            const params = new URLSearchParams({ start: start, end: end });
            if (waktu) params.set('waktu', waktu);
            const res = await fetch('{{ route('presensi.detail') }}?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error('Gagal memuat data');
            const json = await res.json();
            return json.names || {};
        }

        function breakdownCardsHtml(names, unit) {
            let html = '';
            Object.keys(STATUS_BOX).forEach(label => {
                const box = STATUS_BOX[label];
                const list = names[label] || {};
                const jmlNama = Object.keys(list).length;
                const listHtml = jmlNama > 0
                    ? groupNamesWithDates(list)
                    : `<span class="text-slate-400 text-[11px] italic">Tidak ada</span>`;
                html += `
                    <div class="border border-slate-200 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-sm" style="color:${box.text}">${label}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:${box.countBg};color:${box.countText}">${jmlNama} ${unit}</span>
                        </div>
                        <div class="space-y-0.5">${listHtml}</div>
                    </div>
                `;
            });
            return html;
        }

        function breakdownLoadingHtml(unit) {
            let html = '';
            Object.keys(STATUS_BOX).forEach(label => {
                const box = STATUS_BOX[label];
                html += `
                    <div class="border border-slate-200 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-sm" style="color:${box.text}">${label}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:${box.countBg};color:${box.countText}">- ${unit}</span>
                        </div>
                        <div class="text-slate-400 text-[11px] italic">Memuat...</div>
                    </div>
                `;
            });
            return html;
        }

        // Status detail
        function showStatusDetail(status) {
            const data = JSON.parse(document.getElementById('statusBreakdownData').textContent);
            const statusData = data.find(s => s.status === status);
            const santris = statusData?.santris || [];

            let content = '';
            if (santris.length === 0) {
                content = '<p class="text-slate-500 text-center py-6">Tidak ada santri dengan status ini</p>';
            } else {
                content = `
                    <p class="text-sm text-slate-600 mb-4">Total: <span class="font-semibold text-slate-900">${santris.length} santri</span></p>
                    <div class="space-y-2">
                        ${santris.map((s, i) => `
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 transition-smooth">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-blue-400 flex items-center justify-center text-white text-xs font-bold">${i + 1}</span>
                                        <div>
                                            <p class="font-semibold text-slate-900 text-sm">${s.nama}</p>
                                            <p class="text-[11px] text-slate-500">${s.kamar} ${s.jabatan}</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold text-white" style="background-color: ${getStatusColor(status)};">${status}</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
            openModal('Detail: ' + status, content);
        }

        // Waktu detail (lazy-load daftar nama)
        function showWaktuDetail(waktu) {
            const data = JSON.parse(document.getElementById('waktuBreakdownData').textContent);
            const detail = data[waktu];
            if (!detail) return;

            const statusColors = { 'Jamaah': '#22c55e', 'Masbuq': '#f97316', 'Izin': '#3b82f6', 'Alfa': '#ef4444' };
            const statusConfig = [
                { key: 'Jamaah', bg: '#ecfdf5', text: '#065f46', countBg: '#d1fae5', countText: '#059669' },
                { key: 'Masbuq', bg: '#fffbeb', text: '#92400e', countBg: '#fde68a', countText: '#d97706' },
                { key: 'Izin', bg: '#eff6ff', text: '#1e40af', countBg: '#bfdbfe', countText: '#2563eb' },
                { key: 'Alfa', bg: '#fef2f2', text: '#991b1b', countBg: '#fee2e2', countText: '#dc2626' },
            ];
            const total = detail.total || 0;

            let barsHtml = '';
            statusConfig.forEach((s, idx) => {
                const count = detail[s.key.toLowerCase()] || 0;
                const pct = total > 0 ? Math.round((count / total) * 100) : 0;
                barsHtml += `
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="text-xs font-medium text-slate-600 text-right" style="width:52px;flex-shrink:0">${s.key}</span>
                        <div class="bg-slate-100 rounded-full" style="flex:1;height:24px;overflow:hidden;min-width:0;">
                            <div class="h-full rounded-full bar-animated-h" style="width: ${Math.max(pct, 2)}%; background-color: ${statusColors[s.key]}; animation-delay: ${idx * 0.15}s;"></div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-600" style="width:62px;text-align:right;flex-shrink:0;white-space:nowrap">${count} (${pct}%)</span>
                    </div>
                `;
            });

            const content = `
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <span class="text-sm text-slate-600">Total Record</span>
                        <span class="text-lg font-bold text-slate-900">${total}</span>
                    </div>
                    <div class="space-y-3">
                        ${barsHtml}
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-800 text-sm">Rincian Kehadiran</h4>
                        <div class="space-y-2" id="waktuBreakdownBox">${breakdownLoadingHtml('orang')}</div>
                    </div>
                </div>
            `;
            openModal('Detail: Sholat ' + waktu, content);

            fetchDetailNames(detail.start, detail.end, waktu)
                .then(names => {
                    const box = document.getElementById('waktuBreakdownBox');
                    if (box) box.innerHTML = breakdownCardsHtml(names, 'orang');
                })
                .catch(() => {
                    const box = document.getElementById('waktuBreakdownBox');
                    if (box) box.innerHTML = '<p class="text-rose-500 text-[11px] italic">Gagal memuat daftar nama.</p>';
                });
        }

        // Line chart detail (lazy-load daftar nama)
        function showLineChartDetail(index) {
            const data = JSON.parse(document.getElementById('lineChartData').textContent);
            const labels = JSON.parse(document.getElementById('lineChartLabels').textContent);
            const item = data[index];
            if (!item) return;

            const pct = item.total > 0 ? Math.round((item.hadir / item.total) * 100) : 0;
            const unit = IS_PERSONAL_MODE ? 'kali' : 'orang';

            const content = `
                <div class="space-y-4">
                    <div class="text-center p-4 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">${item.label || labels[index]}</p>
                        <p class="text-3xl font-bold text-teal-600">${pct}%</p>
                        <p class="text-xs text-slate-500 mt-1">Tingkat kehadiran berjamaah (${IS_PERSONAL_MODE ? 'Berjamaah' : 'Jamaah'}/total)</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-50 p-3 rounded-xl text-center">
                            <p class="text-2xl font-bold text-teal-600">${item.hadir}</p>
                            <p class="text-xs text-slate-600">Hadir (${IS_PERSONAL_MODE ? 'Berjamaah' : 'Jamaah'})</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl text-center">
                            <p class="text-2xl font-bold text-slate-600">${item.total}</p>
                            <p class="text-xs text-slate-600">Total Record</p>
                        </div>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3">
                        <p class="text-[12px] text-slate-600 leading-relaxed"><span class="font-bold text-emerald-700">Penjelasan:</span> Dari total <span class="font-bold">${item.total}</span> record presensi pada <span class="font-bold">${item.label || labels[index]}</span>, sebanyak <span class="font-bold text-teal-600">${item.hadir}</span> tercatat hadir berjamaah (status ${IS_PERSONAL_MODE ? 'Berjamaah' : 'Jamaah'}). Jadi tingkat kehadiran = ${item.hadir} &divide; ${item.total} &times; 100 = <span class="font-bold text-emerald-700">${pct}%</span>.</p>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-800 text-sm">Rincian Kehadiran</h4>
                        <div class="space-y-2" id="lineBreakdownBox">${breakdownLoadingHtml(unit)}</div>
                    </div>
                </div>
            `;
            openModal('Detail: ' + (item.label || labels[index]), content);

            fetchDetailNames(item.start, item.end, null)
                .then(names => {
                    const box = document.getElementById('lineBreakdownBox');
                    if (box) box.innerHTML = breakdownCardsHtml(names, unit);
                })
                .catch(() => {
                    const box = document.getElementById('lineBreakdownBox');
                    if (box) box.innerHTML = '<p class="text-rose-500 text-[11px] italic">Gagal memuat daftar nama.</p>';
                });
        }

        // Toast kecil utk feedback tombol salin
        function chartCopyToast(msg, type) {
            let c = document.getElementById('chart-copy-toast');
            if (!c) {
                c = document.createElement('div');
                c.id = 'chart-copy-toast';
                Object.assign(c.style, { position: 'fixed', top: '24px', left: '50%', transform: 'translateX(-50%)', zIndex: '100002', padding: '10px 18px', borderRadius: '10px', fontSize: '13px', fontWeight: '600', color: '#fff', boxShadow: '0 8px 20px rgba(0,0,0,.18)', transition: 'opacity .3s', pointerEvents: 'none', whiteSpace: 'nowrap' });
                document.body.appendChild(c);
            }
            c.style.background = type === 'error' ? '#dc2626' : '#059669';
            c.textContent = msg;
            c.style.opacity = '1';
            clearTimeout(c._t);
            c._t = setTimeout(function () { c.style.opacity = '0'; }, 2800);
        }

        // Buang gelar di depan/di belakang nama (mis. "K.H. Ahmad" -> "Ahmad", "Fauzi, S.Pd." -> "Fauzi")
        function stripGelar(raw) {
            let s = String(raw || '').trim().replace(/\s+/g, ' ');
            if (!s) return s;
            // Gelar belakang setelah koma: ulangi sampai habis
            s = s.replace(/(?:,\s*(?:cum laude|[A-Za-zÀ-ž]+(?:\.\s?)?(?:\s?[A-Za-zÀ-ž]*\.?)*))+$/g, '');
            // Gelar depan: ulangi sampai habis
            s = s.replace(/^(?:(?:K\.?\s?H\.?|H\.?|Hj\.?|Haji|Hajjah|Ustadz|Ustadzah|Ustaz|Ustazah|Ust\.?|Ustdz\.?|Dr\.?|Drs\.?|Dra\.?|Prof\.?|Ir\.?|Lc\.?|Nyai)(?=\s|$)\s*)+/i, '');
            return s.trim();
        }

        // Copy teks via metode klasik (sinkron, tanpa permission) lalu fallback Clipboard API
        function copyTextVia(text) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;top:0;left:0;width:2em;height:2em;padding:0;border:0;opacity:0.01;font-size:14px;z-index:-1000;';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            ta.setSelectionRange(0, text.length);
            let ok = false;
            try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
            document.body.removeChild(ta);
            return ok;
        }

        // Modal cadangan: tampilkan teks siap salin manual (Ctrl+C / tahan-tahan)
        function showCopyManualModal(text) {
            const bg = document.createElement('div');
            bg.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:100000;display:flex;align-items:center;justify-content:center;padding:20px;';
            const box = document.createElement('div');
            box.style.cssText = 'background:#fff;border-radius:14px;max-width:520px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.35);overflow:hidden;';
            const head = document.createElement('div');
            head.style.cssText = 'background:#7c3aed;color:#fff;padding:14px 18px;font-weight:700;font-size:14px;display:flex;justify-content:space-between;align-items:center;';
            head.innerHTML = 'Salin manual (Ctrl+C) <span style="cursor:pointer;font-size:20px;line-height:1;">&times;</span>';
            head.lastChild.onclick = function () { document.body.removeChild(bg); };
            const body = document.createElement('div');
            body.style.cssText = 'padding:16px;';
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.readOnly = false;
            ta.style.cssText = 'width:100%;height:280px;border:1px solid #e2e8f0;border-radius:10px;padding:10px;font-size:13px;box-sizing:border-box;outline:none;';
            body.appendChild(ta);
            box.appendChild(head);
            box.appendChild(body);
            bg.appendChild(box);
            document.body.appendChild(bg);
            ta.focus();
            ta.select();
            ta.setSelectionRange(0, text.length);
        }

        // Salin daftar nama (Status Kehadiran) sebagai TEKS ke clipboard utk paste di WhatsApp
        async function copyStatusChartToClipboard() {
            try {
                const donut = document.getElementById('statusDonut');
                const card = donut ? donut.closest('.bg-white') : null;
                let waktu = String(typeof waktuSholat !== 'undefined' ? waktuSholat : '').trim();
                let tgl = String(typeof tanggal !== 'undefined' ? tanggal : '').trim();

                // Ambil waktu & tanggal dari data attribute pada keterangan kiri kartu
                if (card) {
                    const sub = card.querySelector('p[data-waktu]');
                    if (sub) {
                        const w = (sub.dataset.waktu || '').trim();
                        const t = (sub.dataset.tgl || '').trim();
                        if (w) waktu = w;
                        if (t) tgl = t;
                    }
                }

                let data = [];
                const jsonEl = document.getElementById('statusBreakdownData');
                if (jsonEl) {
                    try { data = JSON.parse(jsonEl.textContent); } catch (e) { data = []; }
                }
                if (!Array.isArray(data) || data.length === 0) {
                    chartCopyToast('Belum ada data presensi untuk disalin.', 'error');
                    return;
                }

                // Urutan sama dgn keterangan di samping kiri kartu
                const order = ['Hadir', 'Masbuq', 'Izin', 'Alfa'];
                const lines = [];
                lines.push('Presensi Sholat ' + waktu.toUpperCase());
                if (tgl) lines.push(tgl);
                lines.push('');
                data.forEach(function (s) {
                    if (order.indexOf(s.status) === -1) return;
                    const santris = (s && s.santris) || [];
                    if (santris.length === 0) return;
                    lines.push('\u2022 ' + s.status + ' (' + santris.length + ' orang):');
                    santris.forEach(function (p, i) {
                        let nm = stripGelar(p.nama || '');
                        if (!nm) nm = '?';
                        lines.push((i + 1) + '. ' + nm);
                    });
                    lines.push('');
                });

                const text = lines.join('\n');

                // 1) copy sinkron (paling andal)
                let done = copyTextVia(text);
                // 2) kalau gagal, tunggu Clipboard API (async) sampai benar2 selesai
                if (!done && navigator.clipboard && navigator.clipboard.writeText) {
                    try {
                        await navigator.clipboard.writeText(text);
                        done = true;
                    } catch (e) { done = false; }
                }

                if (done) {
                    chartCopyToast('Daftar nama disalin! Paste di WhatsApp (Ctrl+V).');
                } else {
                    showCopyManualModal(text);
                    chartCopyToast('Tekan Ctrl+C di kotak yang muncul untuk menyalin.', 'error');
                }
            } catch (e) {
                console.error(e);
                chartCopyToast('Gagal menyalin teks: ' + (e && e.message ? e.message : e), 'error');
            }
        }

        // Tren 6 bulan detail (lazy-load daftar nama)
        function showTrenDetail(index) {
            const allData = JSON.parse(document.getElementById('trenDetailData').textContent);
            const item = allData[index];
            if (!item) return;

            const pct = item.total > 0 ? Math.round((item.hadir / item.total) * 100) : 0;

            const content = `
                <div class="space-y-4">
                    <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">${item.label}</p>
                        <p class="text-3xl font-bold text-blue-600">${pct}%</p>
                        <p class="text-xs text-slate-500 mt-1">Tingkat kehadiran berjamaah bulan ini</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-50 p-3 rounded-xl text-center">
                            <p class="text-2xl font-bold text-teal-600">${item.hadir}</p>
                            <p class="text-xs text-slate-600">Hadir (Jamaah)</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl text-center">
                            <p class="text-2xl font-bold text-slate-600">${item.total}</p>
                            <p class="text-xs text-slate-600">Total Record</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                        <p class="text-[12px] text-slate-600 leading-relaxed"><span class="font-bold text-blue-700">Penjelasan:</span> Dari total <span class="font-bold">${item.total}</span> record presensi pada bulan ${item.label}, sebanyak <span class="font-bold text-teal-600">${item.hadir}</span> tercatat hadir berjamaah, sehingga tingkat kehadiran mencapai <span class="font-bold text-blue-600">${pct}%</span>.</p>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-800 text-sm">Rincian Kehadiran</h4>
                        <div class="space-y-2" id="trenBreakdownBox">${breakdownLoadingHtml('orang')}</div>
                    </div>
                </div>
            `;
            openModal('Detail Tren: ' + item.label, content);

            fetchDetailNames(item.start, item.end, null)
                .then(names => {
                    const box = document.getElementById('trenBreakdownBox');
                    if (box) box.innerHTML = breakdownCardsHtml(names, 'orang');
                })
                .catch(() => {
                    const box = document.getElementById('trenBreakdownBox');
                    if (box) box.innerHTML = '<p class="text-rose-500 text-[11px] italic">Gagal memuat daftar nama.</p>';
                });
        }

        // Rating detail per hari
        function showRatingDetail(nama, waktu, detail) {
            const statusIcons = {
                'Jamaah': '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-teal-600"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>',
                'Masbuq': '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-orange-100 text-orange-600"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 11.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3A1 1 0 0011 11.586V7z" clip-rule="evenodd"/></svg></span>',
                'Izin': '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-600"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg></span>',
                'Alfa': '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 text-red-500"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></span>'
            };

            let rows = '';
            if (detail.length === 0) {
                rows = '<p class="text-slate-400 text-center py-4 text-sm">Belum ada data presensi</p>';
            } else {
                rows = detail.map((d, i) => `
                    <div class="flex items-center justify-between py-1.5 ${i < detail.length - 1 ? 'border-b border-slate-200' : ''}">
                        <div class="flex items-center gap-2">
                            ${statusIcons[d.status] || ''}
                            <span class="text-[13px] font-medium text-slate-700">${d.status}</span>
                        </div>
                        <span class="text-[12px] text-slate-500">${d.tanggal}</span>
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
                        <div class="flex-1 bg-emerald-50 rounded-lg p-2"><p class="text-lg font-bold text-teal-600">${hadir}</p><p class="text-[10px] text-slate-500">Hadir</p></div>
                        <div class="flex-1 bg-orange-50 rounded-lg p-2"><p class="text-lg font-bold text-orange-600">${masbuq}</p><p class="text-[10px] text-slate-500">Masbuq</p></div>
                        <div class="flex-1 bg-blue-50 rounded-lg p-2"><p class="text-lg font-bold text-blue-600">${izin}</p><p class="text-[10px] text-slate-500">Izin</p></div>
                        <div class="flex-1 bg-red-50 rounded-lg p-2"><p class="text-lg font-bold text-red-500">${alfa}</p><p class="text-[10px] text-slate-500">Alfa</p></div>
                    </div>
                    <div class="max-h-64 overflow-y-auto pr-1">${rows}</div>
                </div>
            `;
            openModal('Rincian ' + nama + ' — ' + waktu, content);
        }

        function showSantriRating(nama, rating, period) {
            const barColor = rating >= 80 ? 'bg-emerald-500' : rating >= 60 ? 'bg-amber-500' : 'bg-red-500';
            const textColor = rating >= 80 ? 'text-teal-600' : rating >= 60 ? 'text-amber-600' : 'text-red-500';
            const content = `
                <div class="space-y-4 text-center">
                    <div class="text-4xl font-bold ${textColor}">${rating}%</div>
                    <p class="text-sm text-slate-500">Rating kehadiran ${period}</p>
                    <div class="h-3 bg-slate-100 rounded-full overflow-hidden mx-auto max-w-xs">
                        <div class="h-full ${barColor} rounded-full transition-all duration-700" style="width: ${rating}%"></div>
                    </div>
                </div>
            `;
            openModal('Rating ' + nama, content);
        }

        // Update counts real-time
        function updateStatusCounts() {
            const data = JSON.parse(document.getElementById('statusBreakdownData').textContent);
            data.forEach(item => {
                const countEl = document.getElementById(`count-${item.status.toLowerCase()}`);
                if (countEl) countEl.textContent = item.santris.length;
            });
        }
        updateStatusCounts();

        // === CHART FILTER ===
        let currentChartFilter = '{{ $chartFilter }}';

        function switchChartFilter(filter, e) {
            currentChartFilter = filter;
            document.querySelectorAll('.chart-filter-tab').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            document.getElementById('hariRange').classList.toggle('hidden', filter !== 'hari');
            document.getElementById('hariRange').classList.toggle('flex', filter === 'hari');
            document.getElementById('mingguRange').classList.toggle('hidden', filter !== 'minggu');
            document.getElementById('mingguRange').classList.toggle('flex', filter === 'minggu');
            document.getElementById('bulanRange').classList.toggle('hidden', filter !== 'bulan');
            document.getElementById('bulanRange').classList.toggle('flex', filter === 'bulan');
            document.getElementById('tahunRange').classList.toggle('hidden', filter !== 'tahun');
            document.getElementById('tahunRange').classList.toggle('flex', filter === 'tahun');
        }

        function applyChartFilter() {
            const params = new URLSearchParams(window.location.search);
            params.set('chart_filter', currentChartFilter);

            if (currentChartFilter === 'hari') {
                const d = document.getElementById('chartDayDate').value;
                params.delete('chart_start');
                params.delete('chart_end');
                if (d) params.set('chart_start', d);
            } else if (currentChartFilter === 'minggu') {
                const wm = document.getElementById('chartWeekMonth').value;
                if (wm) params.set('chart_end', wm);
                params.delete('chart_start');
            } else if (currentChartFilter === 'bulan') {
                const s = document.getElementById('chartStartMonth').value;
                const e = document.getElementById('chartEndMonth').value;
                if (s) params.set('chart_start', s);
                if (e) params.set('chart_end', e);
            } else {
                params.delete('chart_start');
                params.delete('chart_end');
            }

            window.location.href = '?' + params.toString();
        }

        // Show initial date range if filter is hari/minggu/bulan/tahun
        if (currentChartFilter === 'hari') {
            document.getElementById('hariRange').classList.remove('hidden');
            document.getElementById('hariRange').classList.add('flex');
        } else if (currentChartFilter === 'minggu') {
            document.getElementById('mingguRange').classList.remove('hidden');
            document.getElementById('mingguRange').classList.add('flex');
        } else if (currentChartFilter === 'bulan') {
            document.getElementById('bulanRange').classList.remove('hidden');
            document.getElementById('bulanRange').classList.add('flex');
        } else if (currentChartFilter === 'tahun') {
            document.getElementById('tahunRange').classList.remove('hidden');
            document.getElementById('tahunRange').classList.add('flex');
        }

        // === PER ORANG FILTER ===
        let currentPerOrangFilter = '{{ $perOrangPeriode }}';

        function switchPerOrangFilter(filter, e) {
            currentPerOrangFilter = filter;
            document.querySelectorAll('.perorang-filter-tab').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            document.getElementById('perOrangMingguRange').classList.toggle('hidden', filter !== 'minggu');
            document.getElementById('perOrangMingguRange').classList.toggle('flex', filter === 'minggu');
            document.getElementById('perOrangBulanRange').classList.toggle('hidden', filter !== 'bulan');
            document.getElementById('perOrangBulanRange').classList.toggle('flex', filter === 'bulan');
            document.getElementById('perOrangTanggalRange').classList.toggle('hidden', filter !== 'tanggal');
            document.getElementById('perOrangTanggalRange').classList.toggle('flex', filter === 'tanggal');
        }

        function applyPerOrangFilter() {
            const params = new URLSearchParams(window.location.search);
            params.set('per_orang_periode', currentPerOrangFilter);

            const waktu = document.getElementById('perOrangWaktu').value;
            if (waktu && waktu !== 'all') params.set('per_orang_waktu', waktu);
            else params.delete('per_orang_waktu');

            const keterangan = document.getElementById('perOrangKeterangan').value;
            if (keterangan && keterangan !== 'all') params.set('per_orang_keterangan', keterangan);
            else params.delete('per_orang_keterangan');

            if (currentPerOrangFilter === 'minggu') {
                const s = document.getElementById('perOrangStartDate').value;
                const e = document.getElementById('perOrangEndDate').value;
                if (s) params.set('per_orang_start', s);
                if (e) params.set('per_orang_end', e);
            } else if (currentPerOrangFilter === 'bulan') {
                const s = document.getElementById('perOrangStartMonth').value;
                const e = document.getElementById('perOrangEndMonth').value;
                if (s) params.set('per_orang_start', s);
                if (e) params.set('per_orang_end', e);
            } else if (currentPerOrangFilter === 'tanggal') {
                const s = document.getElementById('perOrangTglStart').value;
                const e = document.getElementById('perOrangTglEnd').value;
                if (s) params.set('per_orang_start', s);
                if (e) params.set('per_orang_end', e);
            }

            window.location.href = '?' + params.toString();
        }

        if (currentPerOrangFilter === 'minggu') {
            document.getElementById('perOrangMingguRange').classList.remove('hidden');
            document.getElementById('perOrangMingguRange').classList.add('flex');
        } else if (currentPerOrangFilter === 'bulan') {
            document.getElementById('perOrangBulanRange').classList.remove('hidden');
            document.getElementById('perOrangBulanRange').classList.add('flex');
        } else if (currentPerOrangFilter === 'tanggal') {
            document.getElementById('perOrangTanggalRange').classList.remove('hidden');
            document.getElementById('perOrangTanggalRange').classList.add('flex');
        }

        function showPerOrangDetail(nama, hadir, masbuq, izin, alfa, totalJadwal, kamar) {
            const totalIsi = hadir + masbuq + izin + alfa;
            const pctJadwal = totalJadwal > 0 ? Math.round((hadir / totalJadwal) * 100) : 0;
            const pctIsi = totalIsi > 0 ? Math.round((hadir / totalIsi) * 100) : 0;

            const content = `
                <div class="space-y-4">
                    <div class="text-center p-4 bg-gradient-to-br from-rose-50 to-pink-50 rounded-xl">
                        <p class="text-sm font-semibold text-slate-800">${nama}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">${kamar}</p>
                        <p class="text-3xl font-bold text-teal-600 mt-2">${pctJadwal}%</p>
                        <p class="text-xs text-slate-500 mt-1">Persentase berjamaah dari total jadwal (${totalJadwal})</p>
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        <div class="bg-emerald-50 p-3 rounded-xl text-center">
                            <p class="text-xl font-bold text-teal-600">${hadir}</p>
                            <p class="text-[10px] text-slate-500">Berjamaah</p>
                        </div>
                        <div class="bg-orange-50 p-3 rounded-xl text-center">
                            <p class="text-xl font-bold text-orange-500">${masbuq}</p>
                            <p class="text-[10px] text-slate-500">Masbuq</p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-xl text-center">
                            <p class="text-xl font-bold text-blue-600">${izin}</p>
                            <p class="text-[10px] text-slate-500">Izin</p>
                        </div>
                        <div class="bg-red-50 p-3 rounded-xl text-center">
                            <p class="text-xl font-bold text-red-500">${alfa}</p>
                            <p class="text-[10px] text-slate-500">Alfa</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-slate-500 w-20">Berjamaah</span>
                            <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="h-full rounded-full bg-emerald-500 flex items-center pl-2" style="width: ${Math.min(Math.max(hadir / Math.max(1, totalJadwal) * 100, 2), 100)}%">
                                    ${hadir / Math.max(1, totalJadwal) * 100 > 15 ? '<span class="text-[9px] font-bold text-white">' + hadir + '</span>' : ''}
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-600 w-10 text-right">${Math.round(Math.min(hadir / Math.max(1, totalJadwal) * 100, 100))}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-slate-500 w-20">Masbuq</span>
                            <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="h-full rounded-full bg-orange-400 flex items-center pl-2" style="width: ${Math.min(Math.max(masbuq / Math.max(1, totalJadwal) * 100, 2), 100)}%">
                                    ${masbuq / Math.max(1, totalJadwal) * 100 > 15 ? '<span class="text-[9px] font-bold text-white">' + masbuq + '</span>' : ''}
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-600 w-10 text-right">${Math.round(Math.min(masbuq / Math.max(1, totalJadwal) * 100, 100))}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-slate-500 w-20">Izin</span>
                            <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="h-full rounded-full bg-blue-500 flex items-center pl-2" style="width: ${Math.min(Math.max(izin / Math.max(1, totalJadwal) * 100, 2), 100)}%">
                                    ${izin / Math.max(1, totalJadwal) * 100 > 15 ? '<span class="text-[9px] font-bold text-white">' + izin + '</span>' : ''}
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-600 w-10 text-right">${Math.round(Math.min(izin / Math.max(1, totalJadwal) * 100, 100))}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-slate-500 w-20">Alfa</span>
                            <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="h-full rounded-full bg-red-500 flex items-center pl-2" style="width: ${Math.min(Math.max(alfa / Math.max(1, totalJadwal) * 100, 2), 100)}%">
                                    ${alfa / Math.max(1, totalJadwal) * 100 > 15 ? '<span class="text-[9px] font-bold text-white">' + alfa + '</span>' : ''}
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-600 w-10 text-right">${Math.round(Math.min(alfa / Math.max(1, totalJadwal) * 100, 100))}%</span>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-[11px] text-slate-600 leading-relaxed">
                        <span class="font-bold text-slate-800">Ringkasan:</span>
                        Dari <span class="font-bold">${totalJadwal}</span> jadwal sholat, <span class="font-bold text-teal-600">${hadir}</span> berjamaah,
                        <span class="font-bold text-orange-500">${masbuq}</span> masbuq,
                        <span class="font-bold text-blue-600">${izin}</span> izin, dan
                        <span class="font-bold text-red-500">${alfa}</span> alfa.
                        Dari yang sudah diisi (${totalIsi} kali), tingkat kehadiran <span class="font-bold text-teal-600">${pctIsi}%</span>.
                    </div>
                </div>
            `;
            openModal('Detail: ' + nama, content);
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
    </script>
</body>
</html>
