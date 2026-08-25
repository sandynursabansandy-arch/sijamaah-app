<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Alfa â€” SIJAMAAH</title>
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

        tbody tr { animation: slideUp 0.5s ease-out; }
        tbody tr:nth-child(1) { animation-delay: 0s; }
        tbody tr:nth-child(2) { animation-delay: 0.05s; }
        tbody tr:nth-child(3) { animation-delay: 0.1s; }
        tbody tr:nth-child(4) { animation-delay: 0.15s; }
        tbody tr:nth-child(5) { animation-delay: 0.2s; }
        tbody tr:nth-child(n+6) { animation-delay: 0.25s; }

        .ranking-scroll {
            max-height: 580px; overflow: auto; scrollbar-width: thin;
            scrollbar-color: #ef4444 #e2e8f0;
        }
        .ranking-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .ranking-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 9999px; }
        .ranking-scroll::-webkit-scrollbar-thumb { background: #ef4444; border-radius: 9999px; }
        .ranking-scroll thead th {
            position: sticky; top: 0; z-index: 10;
            background: #ef4444; color: #ffffff;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.12);
        }
        .ranking-scroll thead tr { background: transparent; }

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

        .rank-medal { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; }
        .rank-1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #78350f; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4); }
        .rank-2 { background: linear-gradient(135deg, #d1d5db, #9ca3af); color: #374151; box-shadow: 0 2px 8px rgba(156, 163, 175, 0.4); }
        .rank-3 { background: linear-gradient(135deg, #f59e0b, #d97706); color: #78350f; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.4); }
        .rank-other { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        .alfa-bar { height: 8px; border-radius: 4px; background: linear-gradient(90deg, #ef4444, #dc2626); transition: width 0.6s ease-out; }

        /* ---- Podium Section (Mobile-first, Horizontal) ---- */
        .podium-wrapper {
            display: flex; flex-direction: row; align-items: flex-start;
            justify-content: center; gap: 6px; max-width: 480px;
            margin: 0 auto 24px; padding: 36px 0 0;
        }
        .podium-card {
            flex: 1; min-width: 0; max-width: 140px; min-height: 200px;
            background: #fff; border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: visible; position: relative; text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex; flex-direction: column; justify-content: flex-end;
        }
        .podium-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }

        /* Staggered Top Podium */
        .podium-1 { margin-top: 0; }
        .podium-2 { margin-top: 20px; }
        .podium-3 { margin-top: 40px; }

        /* Badge Peringkat */
        .podium-badge {
            position: absolute; top: -16px; left: 50%; transform: translateX(-50%);
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; color: #fff;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2); z-index: 2;
        }
        .podium-1 .podium-badge {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border: 3px solid #fff; width: 38px; height: 38px; font-size: 16px; top: -18px;
        }
        .podium-2 .podium-badge {
            background: linear-gradient(135deg, #9ca3af, #6b7280); border: 3px solid #fff;
        }
        .podium-3 .podium-badge {
            background: linear-gradient(135deg, #fb923c, #f97316); border: 3px solid #fff;
        }

        /* Isi Kartu */
        .podium-body {
            flex: 1; padding: 28px 6px 8px;
            display: flex; flex-direction: column; justify-content: flex-start;
            align-items: center;
        }

        /* Avatar */
        .podium-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 18px; color: #fff;
            margin: 0 auto 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .podium-1 .podium-avatar {
            width: 52px; height: 52px; font-size: 22px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
        }
        .podium-2 .podium-avatar {
            background: linear-gradient(135deg, #9ca3af, #6b7280);
        }
        .podium-3 .podium-avatar {
            background: linear-gradient(135deg, #fb923c, #f97316);
        }

        /* Nama & Detail */
        .podium-name {
            font-weight: 700; font-size: 12px; color: #1e293b;
            line-height: 1.3; margin-bottom: 2px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            padding: 0 4px;
        }
        .podium-detail {
            font-size: 10px; color: #94a3b8; line-height: 1.4;
            margin-bottom: 10px; padding: 0 4px;
        }
        .podium-detail strong { color: #64748b; font-weight: 600; }

        /* Bar Berwarna di Bawah */
        .podium-bar {
            padding: 8px 0; font-weight: 800; font-size: 16px;
            color: #fff; border-radius: 0 0 14px 14px;
            margin-top: auto;
        }
        .podium-1 .podium-bar {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            font-size: 18px; padding: 10px 0;
        }
        .podium-2 .podium-bar {
            background: linear-gradient(135deg, #9ca3af, #6b7280);
        }
        .podium-3 .podium-bar {
            background: linear-gradient(135deg, #fb923c, #f97316);
        }

        /* Border Podium */
        .podium-1 { border: 2px solid #fbbf24; }
        .podium-2 { border: 2px solid #d1d5db; }
        .podium-3 { border: 2px solid #fdba74; }

        /* Responsive: Tablet & Desktop */
        @media (min-width: 640px) {
            .podium-wrapper { gap: 16px; max-width: 560px; padding-top: 44px; }
            .podium-card { max-width: 170px; border-radius: 18px; min-height: 240px; }
            .podium-body { padding: 32px 12px 10px; }
            .podium-name { font-size: 14px; }
            .podium-detail { font-size: 11px; }
            .podium-avatar { width: 52px; height: 52px; font-size: 20px; }
            .podium-1 .podium-avatar { width: 60px; height: 60px; font-size: 24px; }
            .podium-badge { width: 36px; height: 36px; font-size: 15px; top: -18px; }
            .podium-1 .podium-badge { width: 42px; height: 42px; font-size: 18px; top: -20px; }
            .podium-1 { margin-top: 0; }
            .podium-2 { margin-top: 28px; }
            .podium-3 { margin-top: 56px; }
        }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-red-50 text-slate-800 min-h-screen pb-8">
                @include('partials.app-header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">
        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-400 to-rose-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Ranking Alfa</h1>
                <p class="text-[11px] text-slate-400">Santri dengan tingkat alfa tertinggi</p>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="GET" action="{{ route('presensi.rankingAlfa') }}" class="filter-gradient rounded-xl p-3 mb-5 animate-fade-in shadow-sm">
            <div class="flex flex-wrap items-end gap-2.5">
                <div class="animate-slide-in">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $start_date }}" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-red-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                </div>
                <div class="animate-slide-in" style="animation-delay: 0.05s">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $end_date }}" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-red-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                </div>
                <div class="animate-slide-in" style="animation-delay: 0.1s">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Asrama / Rayon</label>
                    <select name="kamar_id" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-red-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                        <option value="all" {{ $kamarId == 'all' ? 'selected' : '' }}>Semua Rayon</option>
                        @foreach($daftarKamar as $kamar)
                            <option value="{{ $kamar->id }}" {{ $kamarId == $kamar->id ? 'selected' : '' }}>{{ $kamar->nama_kamar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ml-auto animate-slide-in flex gap-2" style="animation-delay: 0.15s">
                    <button type="submit" class="bg-red-500 text-white py-1.5 px-5 rounded-md font-semibold hover:bg-red-600 shadow transition-smooth text-[13px] h-[34px] box-border">Tampilkan</button>
                </div>
            </div>
        </form>

        <!-- Summary Cards -->
        @php
            $totalAlfaAll = $rankingData->sum('alfa_count');
            $totalSantri = $rankingData->count();
            $santriWithAlfa = $rankingData->filter(fn($r) => $r['alfa_count'] > 0)->count();
            $maxAlfa = $rankingData->max('alfa_count') ?? 1;
        @endphp

        <!-- Podium Top 3 -->
        @if(count($rankingData) >= 3)
            @php
                $top3 = $rankingData->take(3);
                $first = $top3[0];
                $second = $top3[1];
                $third = $top3[2];
            @endphp
            <div class="podium-wrapper animate-slide-up">
                <!-- 2nd Place (Kiri) -->
                <div class="podium-card podium-2" data-rank="2">
                    <div class="podium-badge">2</div>
                    <div class="podium-body">
                        <div class="podium-avatar">{{ substr($second['nama'], 0, 1) }}</div>
                        <div class="podium-name">{{ $second['nama'] }}</div>
                        <div class="podium-detail">{{ $second['kamar'] }} &middot; <strong>Kali Alfa: {{ $second['alfa_count'] }}</strong></div>
                    </div>
                    <div class="podium-bar">{{ $second['alfa_count'] }}</div>
                </div>
                <!-- 1st Place (Tengah) -->
                <div class="podium-card podium-1" data-rank="1">
                    <div class="podium-badge">1</div>
                    <div class="podium-body">
                        <div class="podium-avatar">{{ substr($first['nama'], 0, 1) }}</div>
                        <div class="podium-name">{{ $first['nama'] }}</div>
                        <div class="podium-detail">{{ $first['kamar'] }} &middot; <strong>Kali Alfa: {{ $first['alfa_count'] }}</strong></div>
                    </div>
                    <div class="podium-bar">{{ $first['alfa_count'] }}</div>
                </div>
                <!-- 3rd Place (Kanan) -->
                <div class="podium-card podium-3" data-rank="3">
                    <div class="podium-badge">3</div>
                    <div class="podium-body">
                        <div class="podium-avatar">{{ substr($third['nama'], 0, 1) }}</div>
                        <div class="podium-name">{{ $third['nama'] }}</div>
                        <div class="podium-detail">{{ $third['kamar'] }} &middot; <strong>Kali Alfa: {{ $third['alfa_count'] }}</strong></div>
                    </div>
                    <div class="podium-bar">{{ $third['alfa_count'] }}</div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5 animate-slide-up">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center hover-lift transition-smooth">
                <p class="text-2xl font-extrabold text-red-500">{{ $totalAlfaAll }}</p>
                <p class="text-[11px] text-slate-500 font-medium">Total Alfa</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center hover-lift transition-smooth">
                <p class="text-2xl font-extrabold text-orange-500">{{ $santriWithAlfa }}</p>
                <p class="text-[11px] text-slate-500 font-medium">Santri Bermasalah</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center hover-lift transition-smooth">
                <p class="text-2xl font-extrabold text-slate-600">{{ $totalSantri }}</p>
                <p class="text-[11px] text-slate-500 font-medium">Total Santri</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center hover-lift transition-smooth">
                <p class="text-2xl font-extrabold text-amber-500">{{ $maxAlfa }}</p>
                <p class="text-[11px] text-slate-500 font-medium">Alfa Tertinggi</p>
            </div>
        </div>

        <!-- Ranking Table -->
        @if(count($rankingData) > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in border border-slate-100">
                <div class="overflow-x-auto ranking-scroll">
                    <table class="w-full min-w-[600px]">
                        <thead>
                            <tr class="bg-gradient-to-r from-red-500 to-red-600 text-white">
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[7%]"><span class="th-badge">Rank</span></th>
                                <th class="px-3 py-2.5 text-left text-[13px] font-bold w-[30%]"><span class="th-badge">Nama Santri</span></th>
                                <th class="px-3 py-2.5 text-left text-[13px] font-bold w-[18%]"><span class="th-badge">Rayon</span></th>
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[10%]"><span class="th-badge">Alfa</span></th>
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[12%]"><span class="th-badge">Poin</span></th>
                                <th class="px-3 py-2.5 text-left text-[13px] font-bold w-[23%]"><span class="th-badge">Persentase</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rankingData as $index => $item)
                                @php
                                    $rank = $index + 1;
                                    $rankClass = match($rank) { 1 => 'rank-1', 2 => 'rank-2', 3 => 'rank-3', default => 'rank-other' };
                                    $barWidth = $maxAlfa > 0 ? round(($item['alfa_count'] / $maxAlfa) * 100) : 0;
                                    $alfaPercentage = $totalAlfaAll > 0 ? round(($item['alfa_count'] / $totalAlfaAll) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-smooth {{ $rank <= 3 ? 'bg-red-50/30' : '' }}">
                                    <td class="px-3 py-3 text-center">
                                        <div class="rank-medal {{ $rankClass }}">{{ $rank }}</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-red-400 to-orange-400 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm">
                                                {{ substr($item['nama'], 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate text-[13px]">{{ $item['nama'] }}</p>
                                                @if($item['jabatan'])
                                                    <p class="text-[11px] text-emerald-600 font-medium truncate">{{ $item['jabatan'] }}</p>
                                                @else
                                                    <p class="text-[11px] text-slate-400 truncate">{{ $item['nis'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="text-[12px] text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">{{ $item['kamar'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-0.5 rounded-full text-sm font-extrabold {{ $item['alfa_count'] > 0 ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600' }}">
                                            {{ $item['alfa_count'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @php
                                            $poinFlag = $item['poin'] >= 25;
                                            $poinTitle = "Alfa {$item['alfa_count']}Ã—5 + Masbuq {$item['masbuq_count']}Ã—2 + Izin {$item['izin_count']}Ã—1 = {$item['poin']} poin";
                                        @endphp
                                        <span title="{{ $poinTitle }}" class="inline-flex items-center gap-1 justify-center min-w-[44px] px-2 py-0.5 rounded-full text-sm font-extrabold {{ $poinFlag ? 'bg-red-600 text-white ring-2 ring-red-300' : ($item['poin'] > 0 ? 'bg-orange-100 text-orange-600' : 'bg-slate-100 text-slate-400') }}">
                                            {{ $item['poin'] }}
                                            @if($poinFlag)
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.75 7l2.05 2.4A1 1 0 0116 11H6v4a1 1 0 11-2 0V6z"/></svg>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="alfa-bar" style="width: {{ $barWidth }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-semibold text-slate-500 w-[42px] text-right">{{ $alfaPercentage }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3 text-right text-[11px] text-slate-400 animate-fade-in">
                Dari <strong>{{ \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y') }}</strong> sampai <strong>{{ \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y') }}</strong>
                &middot; Poin pelanggaran: Alfa = 5, Masbuq = 2, Izin = 1 (tandai merah jika &ge; 25 poin)
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-8 text-center animate-fade-in border border-slate-100">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak ada data</h3>
                <p class="text-slate-500 text-sm mb-3">Tidak ada data alfa pada periode dan rayon yang dipilih.</p>
                <a href="{{ route('presensi.rankingAlfa') }}" class="inline-block bg-red-500 text-white px-5 py-2 rounded-lg font-semibold text-sm hover:bg-red-600 transition-smooth shadow">Reset Filter</a>
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
        function confirmLogout() {
            document.getElementById('logoutForm').submit();
        }
    </script>
</body>
</html>
