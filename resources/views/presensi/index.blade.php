<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Presensi Sholat Santri</title>
    <link rel="stylesheet" href="{{ asset('custom-assets/app.css') }}?v={{ filemtime(public_path('custom-assets/app.css')) }}">
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
        .status-btn.masbuq { background-color: #fff7ed; color: #c2410c; border-color: #fb923c; }
        .status-btn.masbuq:hover { background-color: #fb923c; transform: scale(1.05); }
        .status-btn.izin { background-color: #dbeafe; color: #0c4a6e; border-color: #7dd3fc; }
        .status-btn.izin:hover { background-color: #7dd3fc; transform: scale(1.05); }
        .status-btn.alfa { background-color: #e5e7eb; color: #374151; border-color: #d1d5db; }
        .status-btn.alfa:hover { background-color: #d1d5db; transform: scale(1.05); }
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
        .scrollbar-thin { scrollbar-width: thin; scrollbar-color: #d1d5db transparent; }
        .scrollbar-thin::-webkit-scrollbar { width: 5px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 9999px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</head>
        <body class="bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50 text-slate-800 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-5 animate-slide-in relative z-30">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                <div class="flex items-center gap-3 shrink-0">
                    <div class="w-14 h-14 shrink-0 rounded-full bg-white shadow-lg flex items-center justify-center overflow-hidden border-[3px] border-white ring-2 ring-emerald-200">
                        <img src="{{ asset('images/image.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                            Dashboard Sholat Berjamaah
                        </h1>
                        <p class="text-slate-500 text-xs">Centang status kehadiran santri dengan cepat</p>
                    </div>
                </div>
                @include('partials.app-sidebar')
            </div>
        </div>

        @if(session('success'))
            <div class="alert-animate mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(!empty($alfaBeruntun))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg animate-fade-in">
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-red-700">Perhatian: {{ count($alfaBeruntun) }} santri alfa beruntun</p>
                        <p class="text-xs text-red-600 mt-0.5 leading-relaxed">
                            @foreach(array_slice($alfaBeruntun, 0, 5) as $s)
                                @if(! $loop->first)<span class="text-red-300">; </span>@endif
                                <span class="font-semibold">{{ $s['nama'] }}</span> ({{ $s['kamar'] }}) &mdash; {{ $s['streak'] }} hari
                            @endforeach
                            @if(count($alfaBeruntun) > 5)<span class="text-red-300">; ...</span>@endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filter Section -->
        <form method="GET" action="{{ route('presensi.index') }}" class="filter-gradient rounded-xl p-3 mb-5 animate-fade-in shadow-sm">
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
                <div class="ml-auto animate-slide-in" style="animation-delay: 0.15s">
                    <button type="submit" class="bg-emerald-500 text-white py-1.5 px-5 rounded-md font-semibold hover:bg-emerald-600 shadow transition-smooth text-[13px] h-[34px] box-border">Tampilkan</button>
                </div>
            </div>
        </form>

        @if(count($santris) > 0)
            @php
                $chartLabels = $chartData['presensiBulanan']['labels'];
                $chartValues = $chartData['presensiBulanan']['values'];
                $chartMax = max(100, max($chartValues ?: [100]));
                $chartTitle = $chartData['presensiBulanan']['title'] ?? 'Kehadiran';
                $chartSubtitle = $chartData['presensiBulanan']['subtitle'] ?? '';
                $chartDetailLabels = $chartData['presensiBulanan']['detailLabels'] ?? $chartLabels;
                $chartDetailData = $chartData['presensiBulanan']['detailData'] ?? [];

                $presensiWaktuLabels = $chartData['presensiPerWaktu']['labels'];
                $presensiWaktuValues = $chartData['presensiPerWaktu']['values'];
                $presensiWaktuSubtitle = $chartData['presensiPerWaktu']['subtitle'] ?? '30 hari terakhir';
                $waktuBreakdownDetail = $chartData['waktuBreakdownDetail'] ?? [];

                $statusKehadiran = $chartData['statusKehadiran'];
                $statusOrder = ['Hadir', 'Masbuq', 'Izin', 'Alfa'];
                $statusColors = [
                    'Hadir' => '#22c55e',
                    'Masbuq' => '#f97316',
                    'Izin' => '#3b82f6',
                    'Alfa' => '#94a3b8',
                ];

                $numLabels = count($chartLabels);
                $labelStep = max(1, ceil($numLabels / 25));
                $chartStartDate = $chartStart ?? ($chartData['dateRange']['start'] ?? date('Y-m-d'));
                $chartEndDate = $chartEnd ?? ($chartData['dateRange']['end'] ?? date('Y-m-d'));
                $chartStartMonth = substr($chartStartDate, 0, 7);
                $chartEndMonth = substr($chartEndDate, 0, 7);

                $ratingStartDate = \Carbon\Carbon::parse($tanggal)->subDays($periodRating - 1)->format('d');
                $ratingEndDate = \Carbon\Carbon::parse($tanggal)->format('d');
                $ratingEndMonth = \Carbon\Carbon::parse($tanggal)->format('M');
            @endphp

            <!-- Grafik Dashboard -->
            <div class="mt-8 space-y-3 animate-slide-up">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                                <p class="text-[10px] text-slate-400">dari tanggal {{ $ratingStartDate }}-{{ $ratingEndDate }} {{ $ratingEndMonth }} &middot; {{ $santris->count() }} santri</p>
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

                    <!-- Status Kehadiran Donut -->
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
                                <p class="text-[10px] text-slate-400">{{ $waktu }} &middot; {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</p>
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
                                <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
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
                                                    stroke="{{ $statusColors[$status] }}" stroke-width="16"
                                                    stroke-dasharray="{{ sprintf('%.2f', $dashLength) }} {{ sprintf('%.2f', $dashGap) }}"
                                                    stroke-dashoffset="{{ sprintf('%.2f', $offset) }}"
                                                    class="cursor-pointer hover:opacity-80 transition-all duration-200"
                                                    onclick="showStatusDetail('{{ $status }}')">
                                                    <title>{{ $status }}: {{ $cnt }} santri ({{ round($percentage * 100) }}%)</title>
                                                </circle>
                                            @endif
                                        @endforeach
                                    @else
                                        <circle cx="50" cy="50" r="35" fill="transparent" stroke="#e2e8f0" stroke-width="16" />
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
                    </div>
                </div>

                <!-- Tren Kehadiran 6 Bulan -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-800">Tren Kehadiran 6 Bulan</h3>
                            <p class="text-[11px] text-slate-400">Persentase berjamaah per bulan &middot; Klik titik untuk detail</p>
                        </div>
                    </div>
                    @php
                        $trenVals = collect($trenBulanan)->pluck('pct')->all();
                        $trenMax = max(100, max($trenVals ?: [100]));
                        $trenLeft = 40; $trenRight = 600; $trenTop = 34; $trenBottom = 132;
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
                    <svg viewBox="0 0 620 175" class="w-full h-auto tren-chart">
                        <!-- Grid lines -->
                        <g stroke="#e2e8f0" stroke-width="0.5">
                            <line x1="{{ $trenLeft }}" y1="{{ $trenBottom }}" x2="{{ $trenRight }}" y2="{{ $trenBottom }}"/>
                            <line x1="{{ $trenLeft }}" y1="{{ round($trenBottom - (25 / $trenMax) * ($trenBottom - $trenTop), 1) }}" x2="{{ $trenRight }}" y2="{{ round($trenBottom - (25 / $trenMax) * ($trenBottom - $trenTop), 1) }}"/>
                            <line x1="{{ $trenLeft }}" y1="{{ round($trenBottom - (50 / $trenMax) * ($trenBottom - $trenTop), 1) }}" x2="{{ $trenRight }}" y2="{{ round($trenBottom - (50 / $trenMax) * ($trenBottom - $trenTop), 1) }}"/>
                            <line x1="{{ $trenLeft }}" y1="{{ round($trenBottom - (75 / $trenMax) * ($trenBottom - $trenTop), 1) }}" x2="{{ $trenRight }}" y2="{{ round($trenBottom - (75 / $trenMax) * ($trenBottom - $trenTop), 1) }}"/>
                            <line x1="{{ $trenLeft }}" y1="{{ $trenTop }}" x2="{{ $trenRight }}" y2="{{ $trenTop }}"/>
                        </g>

                        <!-- Y-axis labels -->
                        <g fill="#94a3b8" font-family="system-ui, sans-serif">
                            <text x="22" y="{{ $trenBottom + 4 }}" text-anchor="end">0%</text>
                            <text x="22" y="{{ round($trenBottom - (25 / $trenMax) * ($trenBottom - $trenTop), 1) + 4 }}" text-anchor="end">25%</text>
                            <text x="22" y="{{ round($trenBottom - (50 / $trenMax) * ($trenBottom - $trenTop), 1) + 4 }}" text-anchor="end">50%</text>
                            <text x="22" y="{{ round($trenBottom - (75 / $trenMax) * ($trenBottom - $trenTop), 1) + 4 }}" text-anchor="end">75%</text>
                            <text x="22" y="{{ $trenTop + 4 }}" text-anchor="end">100%</text>
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
                                <title>{{ $p['label'] }}: {{ $p['pct'] }}% — klik untuk detail</title>
                            </circle>
                            <!-- Value label -->
                            <text x="{{ $p['x'] }}" y="{{ $p['y'] - 11 }}" text-anchor="middle" fill="#2563eb" class="chart-val">{{ $p['pct'] }}%</text>
                        @endforeach

                        <!-- X-axis labels -->
                        <g fill="#64748b" font-family="system-ui, sans-serif">
                            @foreach ($trenPoints as $p)
                                <text x="{{ $p['x'] }}" y="160" text-anchor="middle">{{ $p['label'] }}</text>
                            @endforeach
                        </g>
                    </svg>
                </div>

                <!-- Kehadiran Chart + Filter Tabs -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-md">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[14px] font-bold text-slate-800">{{ $chartTitle }}</h3>
                                <p class="text-[11px] text-slate-400">{{ $chartSubtitle }} &middot; Klik untuk detail</p>
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center gap-2">
                            @php $filters = ['minggu' => 'Per Minggu', 'bulan' => 'Per Bulan', 'tahun' => 'Per Tahun']; @endphp
                            @foreach($filters as $key => $label)
                                <button type="button" class="filter-tab chart-filter-tab {{ $chartFilter === $key ? 'active' : '' }}" onclick="switchChartFilter('{{ $key }}', event)">{{ $label }}</button>
                            @endforeach
                            <div id="mingguRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg p-2">
                                <input type="date" id="chartStartDate" value="{{ $chartStartDate }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                                <span class="text-[10px] text-slate-400 shrink-0">s/d</span>
                                <input type="date" id="chartEndDate" value="{{ $chartEndDate }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                                <button type="button" onclick="applyChartFilter()" class="shrink-0 bg-emerald-500 text-white px-3 py-1 rounded text-[11px] font-semibold hover:bg-emerald-600 transition">Terapkan</button>
                            </div>
                            <div id="bulanRange" class="hidden flex-wrap items-center gap-1.5 w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg p-2">
                                <input type="month" id="chartStartMonth" value="{{ $chartStartMonth }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                                <span class="text-[10px] text-slate-400 shrink-0">s/d</span>
                                <input type="month" id="chartEndMonth" value="{{ $chartEndMonth }}" class="flex-1 min-w-0 sm:max-w-[150px] border border-slate-300 rounded px-2 py-1 text-[11px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                                <button type="button" onclick="applyChartFilter()" class="shrink-0 bg-emerald-500 text-white px-3 py-1 rounded text-[11px] font-semibold hover:bg-emerald-600 transition">Terapkan</button>
                            </div>
                        </div>
                    </div>

                    <svg viewBox="0 0 620 175" class="w-full h-auto line-chart" id="lineChart">
                        <!-- Grid lines -->
                        <g stroke="#e2e8f0" stroke-width="0.5">
                            <line x1="40" y1="132" x2="600" y2="132"/>
                            <line x1="40" y1="105" x2="600" y2="105"/>
                            <line x1="40" y1="78" x2="600" y2="78"/>
                            <line x1="40" y1="50" x2="600" y2="50"/>
                        </g>

                        <!-- Y-axis labels -->
                        <g fill="#94a3b8" font-family="system-ui, sans-serif">
                            <text x="22" y="135" text-anchor="end">0%</text>
                            <text x="22" y="108" text-anchor="end">25%</text>
                            <text x="22" y="81" text-anchor="end">50%</text>
                            <text x="22" y="53" text-anchor="end">75%</text>
                            <text x="22" y="26" text-anchor="end">100%</text>
                        </g>

                        @php
                            $chartWidth = 560;
                            $chartLeft = 40;
                            $chartTop = 23;
                            $chartBottom = 132;
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
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3.5" fill="white" stroke="#10b981" stroke-width="2"
                                        class="chart-clickable" onclick="showLineChartDetail({{ $index }})"
                                        style="animation: countUp 0.4s ease-out {{ $index * 0.08 }}s both;">
                                    <title>{{ $chartDetailLabels[$index] ?? $chartLabels[$index] }}: {{ $chartValues[$index] }}%</title>
                                </circle>
                                <!-- Value label -->
                                <text x="{{ $point['x'] }}" y="{{ max($point['y'] - 10, 17) }}" text-anchor="middle" fill="#059669" class="chart-val"
                                      style="animation: countUp 0.4s ease-out {{ $index * 0.08 + 0.1 }}s both;">{{ $chartValues[$index] }}%</text>
                            </g>
                        @endforeach

                        <!-- X-axis labels -->
                        <g fill="#64748b" font-family="system-ui, sans-serif">
                            @foreach ($chartLabels as $index => $label)
                                @if($index % $labelStep === 0 || $index === $numLabels - 1)
                                    <text x="{{ $chartLeft + ($index * $step) }}" y="158" text-anchor="middle">{{ $label }}</text>
                                @endif
                            @endforeach
                        </g>
                    </svg>
                </div>

                <!-- Hadir per Sholat -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="flex items-center gap-2.5 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-800">Hadir per Sholat</h3>
                            <p class="text-[11px] text-slate-400">{!! $presensiWaktuSubtitle !!} &middot; Klik untuk detail</p>
                        </div>
                    </div>

                    <div class="flex items-end gap-2 h-36">
                        @foreach ($presensiWaktuValues as $index => $value)
                            @php
                                $barHeightPx = max(6, round($value * 1.6));
                                $delay = $index * 0.1;
                            @endphp
                            <div class="flex-1 flex flex-col items-center justify-end cursor-pointer group" style="height: 100%;" onclick="showWaktuDetail('{{ $presensiWaktuLabels[$index] }}')">
                                <span class="text-[11px] font-bold text-slate-700 mb-1">{{ $value }}%</span>
                                <div class="w-full rounded-t-lg bg-gradient-to-t from-blue-600 to-blue-400 bar-animated group-hover:from-indigo-600 group-hover:to-indigo-400 transition-colors"
                                     style="height: {{ $barHeightPx }}px; animation-delay: {{ $delay }}s;"></div>
                                <span class="text-[11px] text-slate-500 font-medium mt-1">{{ $presensiWaktuLabels[$index] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-8 text-center animate-slide-up">
                <h3 class="text-xl font-bold text-slate-800 mb-1">Tidak ada santri</h3>
                <p class="text-slate-500 text-sm mb-3">Silakan pilih kamar atau tambahkan santri terlebih dahulu.</p>
                @if(auth()->user()?->canManagePresensi())
                    <a href="{{ route('santri.create') }}" class="inline-block bg-emerald-500 text-white px-5 py-2 rounded-lg font-semibold hover-lift text-sm">Tambah Santri</a>
                @endif
            </div>
        @endif

        <!-- Universal Modal -->
        <div id="universalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 animate-fade-in">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto animate-slide-up">
                <div class="sticky top-0 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
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

        function getStatusColor(status) {
            const colors = { 'Hadir': '#22c55e', 'Masbuq': '#f97316', 'Izin': '#3b82f6', 'Alfa': '#94a3b8' };
            return colors[status] || '#64748b';
        }

        function groupNamesWithDates(names) {
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            const grouped = {};
            names.forEach(n => {
                if (!grouped[n.nama]) grouped[n.nama] = [];
                const parts = n.tanggal.split('-');
                grouped[n.nama].push({ y: parseInt(parts[0]), m: parseInt(parts[1]), d: parseInt(parts[2]) });
            });
            return Object.entries(grouped).map(([nama, tglArr]) => {
                tglArr.sort((a, b) => a.y - b.y || a.m - b.m || a.d - b.d);
                const dateStr = tglArr.map(t => t.d + ' ' + months[t.m - 1]).join(', ');
                const yearStr = tglArr.length > 0 ? ' ' + tglArr[0].y : '';
                return `<div class="text-[11px] py-0.5"><span class="font-medium">${nama}</span><span class="opacity-60 ml-1">· ${dateStr}${yearStr}</span></div>`;
            }).join('');
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
                                            <p class="text-[11px] text-slate-500">${s.kamar} &middot; ${s.jabatan}</p>
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

        // Waktu detail
        function showWaktuDetail(waktu) {
            const data = JSON.parse(document.getElementById('waktuBreakdownData').textContent);
            const detail = data[waktu];
            if (!detail) return;

            const statusColors = { 'Jamaah': '#22c55e', 'Masbuq': '#f97316', 'Izin': '#3b82f6', 'Alfa': '#94a3b8' };
            const statusConfig = [
                { key: 'Jamaah', bg: '#ecfdf5', text: '#065f46', countBg: '#d1fae5', countText: '#059669' },
                { key: 'Masbuq', bg: '#fffbeb', text: '#92400e', countBg: '#fde68a', countText: '#d97706' },
                { key: 'Izin', bg: '#eff6ff', text: '#1e40af', countBg: '#bfdbfe', countText: '#2563eb' },
                { key: 'Alfa', bg: '#f8fafc', text: '#475569', countBg: '#e2e8f0', countText: '#64748b' },
            ];
            const total = detail.total || 0;
            const names = detail.names || {};

            let barsHtml = '';
            let breakdownHtml = '';
            statusConfig.forEach((s, idx) => {
                const count = detail[s.key.toLowerCase()] || 0;
                const pct = total > 0 ? Math.round((count / total) * 100) : 0;
                barsHtml += `
                    <div class="flex items-center gap-3">
                        <span class="w-20 text-xs font-medium text-slate-600 text-right">${s.key}</span>
                        <div class="flex-1 bg-slate-100 rounded-full h-6 overflow-hidden">
                            <div class="h-full rounded-full flex items-center pl-2 bar-animated-h" style="width: ${Math.max(pct, 2)}%; background-color: ${statusColors[s.key]}; animation-delay: ${idx * 0.15}s;">
                                ${pct > 10 ? `<span class="text-[10px] font-bold text-white">${count} (${pct}%)</span>` : ''}
                            </div>
                        </div>
                        ${pct <= 10 ? `<span class="text-[10px] font-bold text-slate-500 w-16">${count} (${pct}%)</span>` : ''}
                    </div>
                `;
                const statusNames = names[s.key] || [];
                const listHtml = statusNames.length > 0
                    ? groupNamesWithDates(statusNames)
                    : `<span class="text-slate-400 text-[11px] italic">Tidak ada</span>`;
                breakdownHtml += `
                    <div class="border border-slate-200 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-sm" style="color:${s.text}">${s.key}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:${s.countBg};color:${s.countText}">${statusNames.length} orang</span>
                        </div>
                        <div class="space-y-0.5">${listHtml}</div>
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
                        ${breakdownHtml}
                    </div>
                </div>
            `;
            openModal('Detail: Sholat ' + waktu, content);
        }

        // Line chart detail
        function showLineChartDetail(index) {
            const data = JSON.parse(document.getElementById('lineChartData').textContent);
            const labels = JSON.parse(document.getElementById('lineChartLabels').textContent);
            const item = data[index];
            if (!item) return;

            const pct = item.total > 0 ? Math.round((item.hadir / item.total) * 100) : 0;
            const bk = item.breakdown || {};
            const statusConfig = [
                { key: 'Hadir', bg: '#ecfdf5', text: '#065f46', countBg: '#d1fae5', countText: '#059669' },
                { key: 'Masbuq', bg: '#fffbeb', text: '#92400e', countBg: '#fde68a', countText: '#d97706' },
                { key: 'Izin', bg: '#eff6ff', text: '#1e40af', countBg: '#bfdbfe', countText: '#2563eb' },
                { key: 'Alfa', bg: '#f8fafc', text: '#475569', countBg: '#e2e8f0', countText: '#64748b' },
            ];

            let breakdownHtml = '';
            statusConfig.forEach(s => {
                const names = bk[s.key] || [];
                const listHtml = names.length > 0
                    ? groupNamesWithDates(names)
                    : `<span class="text-slate-400 text-[11px] italic">Tidak ada</span>`;
                breakdownHtml += `
                    <div class="border border-slate-200 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-sm" style="color:${s.text}">${s.key}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:${s.countBg};color:${s.countText}">${names.length} orang</span>
                        </div>
                        <div class="space-y-0.5">${listHtml}</div>
                    </div>
                `;
            });

            const content = `
                <div class="space-y-4">
                    <div class="text-center p-4 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">${item.label || labels[index]}</p>
                        <p class="text-3xl font-bold text-emerald-600">${pct}%</p>
                        <p class="text-xs text-slate-500 mt-1">Tingkat kehadiran</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-50 p-3 rounded-xl text-center">
                            <p class="text-2xl font-bold text-emerald-600">${item.hadir}</p>
                            <p class="text-xs text-slate-600">Hadir (Jamaah)</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl text-center">
                            <p class="text-2xl font-bold text-slate-600">${item.total}</p>
                            <p class="text-xs text-slate-600">Total Record</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-800 text-sm">Rincian Kehadiran</h4>
                        ${breakdownHtml}
                    </div>
                </div>
            `;
            openModal('Detail Kehadiran', content);
        }

        // Tren 6 bulan detail
        function showTrenDetail(index) {
            const allData = JSON.parse(document.getElementById('trenDetailData').textContent);
            const item = allData[index];
            if (!item) return;

            const pct = item.total > 0 ? Math.round((item.hadir / item.total) * 100) : 0;
            const bk = item.breakdown || {};
            const statusConfig = [
                { key: 'Hadir', bg: '#ecfdf5', text: '#065f46', countBg: '#d1fae5', countText: '#059669' },
                { key: 'Masbuq', bg: '#fffbeb', text: '#92400e', countBg: '#fde68a', countText: '#d97706' },
                { key: 'Izin', bg: '#eff6ff', text: '#1e40af', countBg: '#bfdbfe', countText: '#2563eb' },
                { key: 'Alfa', bg: '#fef2f2', text: '#991b1b', countBg: '#fee2e2', countText: '#dc2626' },
            ];

            let breakdownHtml = '';
            statusConfig.forEach(s => {
                const names = bk[s.key] || [];
                const listHtml = names.length > 0
                    ? groupNamesWithDates(names)
                    : `<span class="text-slate-400 text-[11px] italic">Tidak ada</span>`;
                breakdownHtml += `
                    <div class="border border-slate-200 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-sm" style="color:${s.text}">${s.key}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:${s.countBg};color:${s.countText}">${names.length} orang</span>
                        </div>
                        <div class="space-y-0.5">${listHtml}</div>
                    </div>
                `;
            });

            const content = `
                <div class="space-y-4">
                    <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
                        <p class="text-sm text-slate-600 mb-1">${item.label}</p>
                        <p class="text-3xl font-bold text-blue-600">${pct}%</p>
                        <p class="text-xs text-slate-500 mt-1">Tingkat kehadiran berjamaah bulan ini</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-50 p-3 rounded-xl text-center">
                            <p class="text-2xl font-bold text-emerald-600">${item.hadir}</p>
                            <p class="text-xs text-slate-600">Hadir (Jamaah)</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl text-center">
                            <p class="text-2xl font-bold text-slate-600">${item.total}</p>
                            <p class="text-xs text-slate-600">Total Record</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                        <p class="text-[12px] text-slate-600 leading-relaxed"><span class="font-bold text-blue-700">Penjelasan:</span> Dari total <span class="font-bold">${item.total}</span> record presensi pada bulan ${item.label}, sebanyak <span class="font-bold text-emerald-600">${item.hadir}</span> tercatat hadir berjamaah, sehingga tingkat kehadiran mencapai <span class="font-bold text-blue-600">${pct}%</span>.</p>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-800 text-sm">Rincian Kehadiran</h4>
                        ${breakdownHtml}
                    </div>
                </div>
            `;
            openModal('Detail Tren: ' + item.label, content);
        }

        // Rating detail per hari
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

        function showSantriRating(nama, rating, period) {
            const barColor = rating >= 80 ? 'bg-emerald-500' : rating >= 60 ? 'bg-amber-500' : 'bg-red-500';
            const textColor = rating >= 80 ? 'text-emerald-600' : rating >= 60 ? 'text-amber-600' : 'text-red-500';
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
            document.getElementById('mingguRange').classList.toggle('hidden', filter !== 'minggu');
            document.getElementById('mingguRange').classList.toggle('flex', filter === 'minggu');
            document.getElementById('bulanRange').classList.toggle('hidden', filter !== 'bulan');
            document.getElementById('bulanRange').classList.toggle('flex', filter === 'bulan');

            if (filter === 'minggu' || filter === 'bulan') {
                applyChartFilter();
            } else {
                const params = new URLSearchParams(window.location.search);
                params.set('chart_filter', filter);
                params.delete('chart_start');
                params.delete('chart_end');
                window.location.href = '?' + params.toString();
            }
        }

        function applyChartFilter() {
            const params = new URLSearchParams(window.location.search);
            params.set('chart_filter', currentChartFilter);

            if (currentChartFilter === 'minggu') {
                const s = document.getElementById('chartStartDate').value;
                const e = document.getElementById('chartEndDate').value;
                if (s) params.set('chart_start', s);
                if (e) params.set('chart_end', e);
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

        // Show initial date range if filter is minggu or bulan
        if (currentChartFilter === 'minggu') {
            document.getElementById('mingguRange').classList.remove('hidden');
            document.getElementById('mingguRange').classList.add('flex');
        } else if (currentChartFilter === 'bulan') {
            document.getElementById('bulanRange').classList.remove('hidden');
            document.getElementById('bulanRange').classList.add('flex');
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
