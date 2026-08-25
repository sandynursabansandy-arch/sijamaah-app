<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Berjamaah â€” SIJAMAAH</title>
    <link rel="stylesheet" href="{{ asset('custom-assets/app.css') }}?v={{ filemtime(public_path('custom-assets/app.css')) }}">
    <style>
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

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
        tbody tr:nth-child(2) { animation-delay: 0.05s; }
        tbody tr:nth-child(3) { animation-delay: 0.1s; }
        tbody tr:nth-child(4) { animation-delay: 0.15s; }
        tbody tr:nth-child(5) { animation-delay: 0.2s; }
        tbody tr:nth-child(n+6) { animation-delay: 0.25s; }

        .ranking-scroll {
            max-height: 580px; overflow: auto; scrollbar-width: thin;
            scrollbar-color: #10b981 #e2e8f0;
        }
        .ranking-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .ranking-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 9999px; }
        .ranking-scroll::-webkit-scrollbar-thumb { background: #10b981; border-radius: 9999px; }
        .ranking-scroll thead th {
            position: sticky; top: 0; z-index: 10;
            background: #10b981; color: #ffffff;
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

        .hadir-bar { height: 8px; border-radius: 4px; background: linear-gradient(90deg, #10b981, #059669); transition: width 0.6s ease-out; }

        .podium-card {
            text-align: center; padding: 16px 12px; border-radius: 16px;
            background: white; border: 2px solid transparent;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        .podium-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .podium-1 { border-color: #fbbf24; background: linear-gradient(180deg, #fffbeb, #ffffff); }
        .podium-2 { border-color: #d1d5db; background: linear-gradient(180deg, #f9fafb, #ffffff); }
        .podium-3 { border-color: #f59e0b; background: linear-gradient(180deg, #fffbeb, #ffffff); }

        .podium-avatar {
            width: 48px; height: 48px; border-radius: 50%; margin: 0 auto 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 18px; color: white;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen pb-8">
                @include('partials.app-header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">
        <!-- Filter Section -->
        <form method="GET" action="{{ route('presensi.rankingBerjamaah') }}" class="filter-gradient rounded-xl p-3 mb-5 animate-fade-in shadow-sm">
            <div class="flex flex-wrap items-end gap-2.5">
                <div class="animate-slide-in">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $start_date }}" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                </div>
                <div class="animate-slide-in" style="animation-delay: 0.05s">
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $end_date }}" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
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

        @php
            $totalHadirAll = $rankingData->sum('hadir');
            $totalSantri = $rankingData->count();
            $rataRataPersentase = $totalSantri > 0 ? round($rankingData->avg('persentase'), 1) : 0;
            $maxHadir = $rankingData->max('hadir') ?? 1;
        @endphp

        <!-- Podium Top 3 -->
        @if(count($rankingData) >= 3)
            @php
                $top3 = $rankingData->take(3);
                $first = $top3[0];
                $second = $top3[1];
                $third = $top3[2];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6 animate-slide-up items-end">
                <!-- 2nd Place -->
                <div class="podium-card podium-2" style="margin-top: 20px;">
                    <div class="rank-medal rank-2 mx-auto mb-2" style="width:32px;height:32px;font-size:14px;">2</div>
                    <div class="podium-avatar" style="background: linear-gradient(135deg, #9ca3af, #6b7280);">{{ substr($second['nama'], 0, 1) }}</div>
                    <p class="font-bold text-slate-800 text-sm truncate">{{ $second['nama'] }}</p>
                    <p class="text-[10px] text-slate-400 mb-2">{{ $second['kamar'] }}</p>
                    <div class="bg-slate-100 rounded-lg py-1.5 px-2">
                        <p class="text-lg font-extrabold text-emerald-600">{{ $second['hadir'] }}</p>
                        <p class="text-[9px] text-slate-500 uppercase font-semibold">Kali Hadir</p>
                    </div>
                    <p class="text-[11px] font-bold text-emerald-500 mt-1">{{ $second['persentase'] }}%</p>
                </div>
                <!-- 1st Place -->
                <div class="podium-card podium-1">
                    <div class="rank-medal rank-1 mx-auto mb-2" style="width:36px;height:36px;font-size:16px;">1</div>
                    <div class="podium-avatar" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); width:56px; height:56px; font-size:22px;">{{ substr($first['nama'], 0, 1) }}</div>
                    <p class="font-bold text-slate-800 text-sm truncate">{{ $first['nama'] }}</p>
                    <p class="text-[10px] text-slate-400 mb-2">{{ $first['kamar'] }}</p>
                    <div class="bg-amber-50 rounded-lg py-1.5 px-2 border border-amber-200">
                        <p class="text-xl font-extrabold text-amber-600">{{ $first['hadir'] }}</p>
                        <p class="text-[9px] text-slate-500 uppercase font-semibold">Kali Hadir</p>
                    </div>
                    <p class="text-[11px] font-bold text-amber-500 mt-1">{{ $first['persentase'] }}%</p>
                </div>
                <!-- 3rd Place -->
                <div class="podium-card podium-3" style="margin-top: 30px;">
                    <div class="rank-medal rank-3 mx-auto mb-2" style="width:32px;height:32px;font-size:14px;">3</div>
                    <div class="podium-avatar" style="background: linear-gradient(135deg, #f59e0b, #d97706);">{{ substr($third['nama'], 0, 1) }}</div>
                    <p class="font-bold text-slate-800 text-sm truncate">{{ $third['nama'] }}</p>
                    <p class="text-[10px] text-slate-400 mb-2">{{ $third['kamar'] }}</p>
                    <div class="bg-orange-50 rounded-lg py-1.5 px-2 border border-orange-200">
                        <p class="text-lg font-extrabold text-orange-600">{{ $third['hadir'] }}</p>
                        <p class="text-[9px] text-slate-500 uppercase font-semibold">Kali Hadir</p>
                    </div>
                    <p class="text-[11px] font-bold text-orange-500 mt-1">{{ $third['persentase'] }}%</p>
                </div>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5 animate-slide-up">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center hover-lift transition-smooth">
                <p class="text-2xl font-extrabold text-emerald-500">{{ $totalHadirAll }}</p>
                <p class="text-[11px] text-slate-500 font-medium">Total Hadir</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center hover-lift transition-smooth">
                <p class="text-2xl font-extrabold text-amber-500">{{ $rataRataPersentase }}%</p>
                <p class="text-[11px] text-slate-500 font-medium">Rata-rata Kehadiran</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center hover-lift transition-smooth">
                <p class="text-2xl font-extrabold text-slate-600">{{ $totalSantri }}</p>
                <p class="text-[11px] text-slate-500 font-medium">Total Santri</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center hover-lift transition-smooth">
                <p class="text-2xl font-extrabold text-teal-500">{{ $maxHadir }}</p>
                <p class="text-[11px] text-slate-500 font-medium">Hadir Tertinggi</p>
            </div>
        </div>

        <!-- Ranking Table -->
        @if(count($rankingData) > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in border border-slate-100">
                <div class="overflow-x-auto ranking-scroll">
                    <table class="w-full min-w-[780px]">
                        <thead>
                            <tr class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[6%]"><span class="th-badge">Rank</span></th>
                                <th class="px-3 py-2.5 text-left text-[13px] font-bold w-[22%]"><span class="th-badge">Nama Santri</span></th>
                                <th class="px-3 py-2.5 text-left text-[13px] font-bold w-[12%]"><span class="th-badge">Rayon</span></th>
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[8%]"><span class="th-badge">Hadir</span></th>
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[8%]"><span class="th-badge">Masbuq</span></th>
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[8%]"><span class="th-badge">Izin</span></th>
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[8%]"><span class="th-badge">Alfa</span></th>
                                <th class="px-3 py-2.5 text-center text-[13px] font-bold w-[8%]"><span class="th-badge">Persen</span></th>
                                <th class="px-3 py-2.5 text-left text-[13px] font-bold w-[20%]"><span class="th-badge">Grafik</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rankingData as $index => $item)
                                @php
                                    $rank = $index + 1;
                                    $rankClass = match($rank) { 1 => 'rank-1', 2 => 'rank-2', 3 => 'rank-3', default => 'rank-other' };
                                    $barWidth = $maxHadir > 0 ? round(($item['hadir'] / $maxHadir) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-smooth {{ $rank <= 3 ? 'bg-emerald-50/30' : '' }}">
                                    <td class="px-3 py-3 text-center">
                                        <div class="rank-medal {{ $rankClass }}">{{ $rank }}</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-400 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm">
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
                                        <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-0.5 rounded-full text-sm font-extrabold bg-emerald-100 text-emerald-600">
                                            {{ $item['hadir'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="text-[13px] font-bold text-orange-600">{{ $item['masbuq'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="text-[13px] font-bold text-blue-600">{{ $item['izin'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="text-[13px] font-bold {{ $item['alfa'] > 0 ? 'text-red-500' : 'text-slate-400' }}">{{ $item['alfa'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @php
                                            $color = $item['persentase'] >= 80 ? 'emerald' : ($item['persentase'] >= 50 ? 'amber' : 'red');
                                        @endphp
                                        <span class="text-[13px] font-extrabold text-{{ $color }}-600">{{ $item['persentase'] }}%</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="hadir-bar" style="width: {{ $barWidth }}%"></div>
                                            </div>
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
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-8 text-center animate-fade-in border border-slate-100">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak ada data</h3>
                <p class="text-slate-500 text-sm mb-3">Tidak ada data presensi pada periode dan rayon yang dipilih.</p>
                <a href="{{ route('presensi.rankingBerjamaah') }}" class="inline-block bg-emerald-500 text-white px-5 py-2 rounded-lg font-semibold text-sm hover:bg-emerald-600 transition-smooth shadow">Reset Filter</a>
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
