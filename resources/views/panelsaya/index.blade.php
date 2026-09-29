<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Saya — SIJAMAAH</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }

        .cal-day { min-height: 74px; }
        .status-dot { width: 7px; height: 7px; border-radius: 9999px; }

        table.bordered { border-collapse: collapse; width: 100%; }
        table.bordered th, table.bordered td { border: 1px solid #e2e8f0; padding: 5px 6px; text-align: center; font-size: 11px; }
        table.bordered thead th { background: #10b981; color: #fff; font-size: 10px; font-weight: 700; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">

        @if(!$santri)
            <div class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-sm animate-slide-up">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    <div>
                        <p class="font-semibold">Data kehadiran belum tersedia</p>
                        <p class="mt-0.5 text-[12px] text-amber-700">Akun <strong>{{ auth()->user()->name }}</strong> tidak cocok dengan nama santri manapun. Hubungi admin untuk menghubungkan data kehadiran Anda.</p>
                    </div>
                </div>
            </div>
        @else
            <!-- Page Title -->
            <div class="mb-4 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center shadow-md">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Panel Saya</h1>
                    <p class="text-[11px] text-slate-400">Kehadiran pribadi &middot; {{ $santri->nama }}</p>
                </div>
            </div>

            <!-- ===== NOTIFIKASI ===== -->
            @if(!empty($notifs) && count($notifs) > 0)
                <div class="space-y-2 mb-3 animate-slide-up">
                    @foreach($notifs as $notif)
                        @php
                            $nc = match($notif['type']) { 'danger' => ['bg'=>'bg-red-50','border'=>'border-red-200','text'=>'text-red-700','title'=>'text-red-700','icon'=>'text-red-500'], 'success' => ['bg'=>'bg-emerald-50','border'=>'border-emerald-200','text'=>'text-emerald-700','title'=>'text-emerald-700','icon'=>'text-emerald-500'], default => ['bg'=>'bg-amber-50','border'=>'border-amber-200','text'=>'text-amber-700','title'=>'text-amber-700','icon'=>'text-amber-500'] };
                        @endphp
                        <div class="flex items-start gap-3 rounded-xl border {{ $nc['border'] }} {{ $nc['bg'] }} p-3">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $nc['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $notif['icon'] }}"/></svg>
                            <div>
                                <p class="text-[13px] font-bold {{ $nc['title'] }} mb-0.5">{{ $notif['title'] }}</p>
                                <p class="text-[12px] {{ $nc['text'] }} leading-relaxed">{!! $notif['text'] !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- ===== PESAN DARI ADMIN ===== -->
            @if(!is_null($santri) && $pesanMessages->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-3 animate-slide-up" id="pesan-admin">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-teal-500 flex items-center justify-center shadow-md">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-bold text-slate-800">Pesan dari Admin</h3>
                                <p class="text-[11px] text-slate-400">{{ $pesanUnread > 0 ? $pesanUnread . ' belum dibaca' : 'Semua sudah dibaca' }}</p>
                            </div>
                        </div>
                        @if($pesanUnread > 0)
                            <button type="button" onclick="pesanMarkAll()" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-[12px] font-semibold transition">Tandai semua dibaca</button>
                        @endif
                    </div>
                    <div class="space-y-2">
                        @foreach($pesanMessages as $pm)
                            <div class="rounded-xl border p-3 {{ $pm->is_read ? 'border-slate-100' : 'border-amber-200 bg-amber-50' }}" id="pesan-{{ $pm->id }}">
                                <div class="flex items-start gap-2.5">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                            <span class="text-[12px] font-bold text-slate-700">Admin</span>
                                            @if(!$pm->is_read)
                                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold">Baru</span>
                                            @endif
                                        </div>
                                        <p class="text-[13px] text-slate-700 leading-relaxed break-words">{!! nl2br(e($pm->pesan)) !!}</p>
                                        <p class="text-[10px] text-slate-400 mt-1">{{ $pm->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    </div>
                                    <div class="shrink-0 flex flex-col items-end gap-1.5">
                                        @if(!$pm->is_read)
                                            <button type="button" onclick="pesanMarkOne({{ $pm->id }})" class="px-2.5 py-1 rounded-full bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-[11px] font-semibold transition">Sudah dibaca</button>
                                        @endif
                                        <button type="button" onclick="pesanDelete({{ $pm->id }})" class="px-2.5 py-1 rounded-full bg-red-50 hover:bg-red-100 text-red-500 text-[11px] font-semibold transition">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <script>
                    function hideUserNotifDot() {
                        var d = document.getElementById('userNotifDot');
                        if (d) d.classList.add('hidden');
                        if (window.escBadge) escBadge();
                    }
                    function pesanMarkOne(id) {
                        hideUserNotifDot();
                        fetch('/pesan-user/' + id + '/baca', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(function(){ location.reload(); }).catch(function(){});
                    }
                    function pesanMarkAll() {
                        hideUserNotifDot();
                        fetch('/pesan-user/baca-semua', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(function(){ location.reload(); }).catch(function(){});
                    }
                    function pesanDelete(id) {
                        if (!confirm('Hapus pesan ini dari panel Anda?')) return;
                        hideUserNotifDot();
                        fetch('/pesan-user/' + id, {method:'DELETE', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(function(){ location.reload(); }).catch(function(){});
                    }
                </script>
            @endif

            <!-- ===== SASARAN & TARGET ===== -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-3 animate-slide-up">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[14px] font-bold text-slate-800">Sasaran &amp; Target Saya</h3>
                        <p class="text-[11px] text-slate-400">Kehadiran berjamaah bulan ini ({{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }})</p>
                    </div>
                    @if($targets['achieved'])
                        <span class="ml-auto px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold">Target tercapai</span>
                    @else
                        <span class="ml-auto px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-[11px] font-bold">Menuju target</span>
                    @endif
                </div>

                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[12px] text-slate-500">Kehadiran berjamaah</span>
                    <span class="text-[13px] font-extrabold {{ $targets['pct'] >= $targets['target'] ? 'text-emerald-600' : ($targets['pct'] >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $targets['pct'] }}%</span>
                </div>
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 {{ $targets['pct'] >= $targets['target'] ? 'bg-emerald-500' : ($targets['pct'] >= 60 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ min(100, $targets['pct']) }}%"></div>
                </div>
                <div class="mt-1.5 text-[11px] text-slate-400">Target: {{ $targets['target'] }}% berjamaah &middot; {{ $targets['jamaah'] }}/{{ $targets['seharusnya'] }} jadwal</div>

                @if(!$targets['achieved'] && $targets['sisaJamaah'] > 0)
                    <div class="mt-2 bg-amber-50 border border-amber-200 rounded-lg p-2.5 text-[12px] text-amber-800">
                        Anda perlu <strong>{{ $targets['sisaJamaah'] }} jadwal</strong> berjamaah lagi bulan ini untuk mencapai target {{ $targets['target'] }}%.
                    </div>
                @elseif($targets['achieved'])
                    <div class="mt-2 bg-emerald-50 border border-emerald-200 rounded-lg p-2.5 text-[12px] text-emerald-800">
                        Alhamdulillah! Kehadiran berjamaah Anda sudah mencapai target bulan ini. Pertahankan istiqamah Anda.
                    </div>
                @endif
            </div>

            <!-- ===== 2. STATISTIK ===== -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3 animate-slide-up">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 text-center">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Rating Bulan Ini</div>
                    <div class="text-3xl font-extrabold mt-1 {{ $monthStats['rating'] >= 80 ? 'text-emerald-600' : ($monthStats['rating'] >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $monthStats['rating'] }}%</div>
                    <div class="text-[10px] text-slate-400 mt-1">{{ $monthStats['jamaah'] }}/{{ $monthStats['seharusnya'] }} berjamaah</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 text-center">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Tercatat</div>
                    <div class="text-3xl font-extrabold mt-1 text-slate-800">{{ $monthStats['terisi'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-1">dari {{ $monthStats['seharusnya'] }} jadwal</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 text-center">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Belum Diisi</div>
                    <div class="text-3xl font-extrabold mt-1 text-slate-400">{{ $monthStats['belumDiisi'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-1">jadwal kosong</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 text-center">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Streak Berjamaah</div>
                    <div class="text-3xl font-extrabold mt-1 text-amber-500">{{ $monthStats['streak']['days'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-1">hari berturut-turut</div>
                </div>
            </div>

            <!-- ===== TINGKAT BULANAN (PRIVAT) ===== -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-3 animate-slide-up">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-800">Tingkat Bulan Ini</h3>
                            <p class="text-[11px] text-slate-400">Penilaian privat &middot; hanya Anda yang melihat ({{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }})</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $monthStats['level']['badge'] }}">{{ $monthStats['level']['label'] }}</span>
                        <a href="{{ route('panel-saya.index', ['bulan' => \Carbon\Carbon::parse($bulan . '-01')->subMonth()->format('Y-m'), 'waktu' => $waktuFilter, 'tanggal' => $historyTanggal]) }}" title="Bulan sebelumnya" class="w-8 h-8 rounded-lg flex items-center justify-center border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-emerald-600 transition">&larr;</a>
                        <a href="{{ route('panel-saya.index', ['bulan' => now()->format('Y-m')]) }}" class="px-2.5 py-1.5 rounded-lg bg-emerald-500 text-white text-[11px] font-semibold hover:bg-emerald-600 transition">Bulan Ini</a>
                        <a href="{{ route('panel-saya.index', ['bulan' => \Carbon\Carbon::parse($bulan . '-01')->addMonth()->format('Y-m'), 'waktu' => $waktuFilter, 'tanggal' => $historyTanggal]) }}" title="Bulan berikutnya" class="w-8 h-8 rounded-lg flex items-center justify-center border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-emerald-600 transition">&rarr;</a>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-xl border-2 {{ $monthStats['level']['border'] }} {{ $monthStats['level']['bg'] }} p-4 mb-3">
                    <div class="w-12 h-12 flex-shrink-0 rounded-full bg-white shadow-sm flex items-center justify-center {{ $monthStats['level']['text'] }}">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div>
                        <div class="text-lg font-extrabold {{ $monthStats['level']['text'] }}">{{ $monthStats['level']['label'] }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Rating {{ $monthStats['rating'] }}% &middot; {{ $monthStats['jamaah'] }}/{{ $monthStats['seharusnya'] }} berjamaah</div>
                        <div class="text-[10px] text-slate-400 mt-1">Terus istiqamah agar tingkat Anda naik</div>
                    </div>
                    @if(!empty($monthStats['rank']))
                        <span class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-semibold" title="Ranking kehadiran berjamaah Anda bulan ini">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            Peringkat #{{ $monthStats['rank']['rank'] }} dari {{ $monthStats['rank']['total'] }} santri
                        </span>
                    @endif
                </div>

                <div>
                    @if($monthStats['terisi'] > 0)
                    <p class="text-[11px] text-slate-500 mb-1">Rating berjamaah per minggu</p>
                    <div class="flex items-end gap-2 py-2">
                        @foreach($monthStats['weekly'] as $wk)
                            @php
                                $wkText = $wk['rating'] >= 90 ? 'text-emerald-600' : ($wk['rating'] >= 75 ? 'text-blue-600' : ($wk['rating'] >= 60 ? 'text-amber-600' : 'text-red-600'));
                                $wkBar = $wk['rating'] >= 90 ? 'bg-emerald-500' : ($wk['rating'] >= 75 ? 'bg-blue-500' : ($wk['rating'] >= 60 ? 'bg-amber-500' : 'bg-red-500'));
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1" title="Minggu {{ $wk['week'] }}: {{ $wk['jamaah'] }}/{{ $wk['target'] }} berjamaah">
                                <span class="text-[11px] font-bold {{ $wkText }}">{{ $wk['rating'] }}%</span>
                                <div class="w-full h-32 flex items-end">
                                    <div class="w-full {{ $wkBar }} rounded-t-lg transition-all duration-700" style="height: {{ max(4, min(100, $wk['rating'])) }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400">Minggu {{ $wk['week'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Tiap minggu = 7 hari (minggu terakhir sisa hari bulan ini); dihitung dari kehadiran Berjamaah. Warna batang mengikuti ranking bulanan.</p>
                    @else
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-600">Belum ada data</p>
                        <p class="text-[11px] text-slate-400">Tidak ada presensi yang diinput pada bulan ini.</p>
                    </div>
                    @endif
                    <div class="flex flex-wrap items-center gap-2 mt-2 pt-3 border-t border-slate-100 text-[10px] text-slate-500">
                        <span class="font-bold text-slate-600">Keterangan ranking (satu bulan):</span>
                        <span class="flex items-center gap-1"><span class="status-dot bg-emerald-500"></span> Istimewa (&ge;90%)</span>
                        <span class="flex items-center gap-1"><span class="status-dot bg-blue-500"></span> Rajin (75&ndash;89%)</span>
                        <span class="flex items-center gap-1"><span class="status-dot bg-amber-500"></span> Cukup (60&ndash;74%)</span>
                        <span class="flex items-center gap-1"><span class="status-dot bg-red-500"></span> Perlu Ditingkatkan (&lt;60%)</span>
                        <span class="ml-auto flex items-center gap-1 font-bold {{ $monthStats['level']['text'] }}">Tingkat bulan ini: {{ $monthStats['level']['label'] }} ({{ $monthStats['rating'] }}%)</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-3">
                <!-- Status breakdown -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 animate-slide-up">
                    <h3 class="text-[12px] font-bold text-slate-600 mb-2">Perincian Status ({{ $bulan }})</h3>
                    <div class="space-y-2">
                        @php
                            $break = [
                                ['label' => 'Berjamaah', 'cnt' => $monthStats['jamaah'], 'color' => 'text-emerald-700 bg-emerald-50', 'dot' => 'bg-emerald-500'],
                                ['label' => 'Masbuq', 'cnt' => $monthStats['masbuq'], 'color' => 'text-orange-700 bg-orange-50', 'dot' => 'bg-orange-500'],
                                ['label' => 'Izin', 'cnt' => $monthStats['izin'], 'color' => 'text-blue-700 bg-blue-50', 'dot' => 'bg-blue-500'],
                                ['label' => 'Alfa', 'cnt' => $monthStats['alfa'], 'color' => 'text-red-700 bg-red-50', 'dot' => 'bg-red-500'],
                                ['label' => 'Belum Diisi', 'cnt' => $monthStats['belumDiisi'], 'color' => 'text-slate-500 bg-slate-100', 'dot' => 'bg-slate-400'],
                            ];
                        @endphp
                        @foreach($break as $b)
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-[12px] text-slate-600">
                                    <span class="status-dot {{ $b['dot'] }}"></span>{{ $b['label'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $b['color'] }}">{{ $b['cnt'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100">
                        <p class="text-[11px] text-slate-500 mb-1.5">Waktu sholat terlemah bulan ini</p>
                        @php $weakest = $monthStats['perWaktu'][0] ?? null; @endphp
                        @if($weakest)
                            <div class="bg-slate-50 rounded-lg p-2 flex items-center justify-between">
                                <span class="text-[12px] font-bold text-slate-700">{{ array_key_first($monthStats['perWaktu']) }}</span>
                                <span class="text-[12px] font-bold {{ $weakest['score'] >= 80 ? 'text-emerald-600' : ($weakest['score'] >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $weakest['score'] }}%</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Per-waktu mini table -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 lg:col-span-2 animate-slide-up">
                    <h3 class="text-[12px] font-bold text-slate-600 mb-2">Kehadiran per Waktu Sholat ({{ $bulan }})</h3>
                    <table class="bordered">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Berjamaah</th>
                                <th>Masbuq</th>
                                <th>Izin</th>
                                <th>Alfa</th>
                                <th>Belum</th>
                                <th>Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sorted = $monthStats['perWaktu']; @endphp
                            @foreach($sorted as $w => $d)
                                <tr>
                                    <td class="font-semibold text-slate-700">{{ $w }}</td>
                                    <td class="text-emerald-700 font-semibold">{{ $d['jamaah'] }}</td>
                                    <td class="text-orange-700">{{ $d['masbuq'] }}</td>
                                    <td class="text-blue-700">{{ $d['izin'] }}</td>
                                    <td class="text-red-700 font-semibold">{{ $d['alfa'] }}</td>
                                    <td class="text-slate-400">{{ $d['belum'] }}</td>
                                    <td class="font-bold {{ $d['score'] >= 80 ? 'text-emerald-600' : ($d['score'] >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $d['score'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="text-[10px] text-slate-400 mt-2">Disusun dari skor terendah ke tertinggi (waktu terlemah di atas).</p>
                </div>
            </div>

            <!-- ===== 1. KALENDER ===== -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-3 animate-slide-up">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-800">Kalender Kehadiran</h3>
                            <p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('panel-saya.index', ['bulan' => \Carbon\Carbon::parse($bulan . '-01')->subMonth()->format('Y-m'), 'waktu' => $waktuFilter, 'tanggal' => $historyTanggal]) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-[12px] font-semibold hover:bg-slate-200 transition">&larr;</a>
                        <a href="{{ route('panel-saya.index', ['bulan' => now()->format('Y-m')]) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-[12px] font-semibold hover:bg-emerald-600 transition">Bulan Ini</a>
                        <a href="{{ route('panel-saya.index', ['bulan' => \Carbon\Carbon::parse($bulan . '-01')->addMonth()->format('Y-m'), 'waktu' => $waktuFilter, 'tanggal' => $historyTanggal]) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-[12px] font-semibold hover:bg-slate-200 transition">&rarr;</a>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 mb-3 text-[11px] text-slate-500">
                    <span class="flex items-center gap-1"><span class="status-dot bg-emerald-500"></span> Berjamaah</span>
                    <span class="flex items-center gap-1"><span class="status-dot bg-orange-500"></span> Masbuq</span>
                    <span class="flex items-center gap-1"><span class="status-dot bg-blue-500"></span> Izin</span>
                    <span class="flex items-center gap-1"><span class="status-dot bg-red-500"></span> Alfa</span>
                </div>

                <div class="grid grid-cols-7 gap-1.5 text-center">
                    @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $hd)
                        <div class="text-[10px] font-bold uppercase text-slate-400 py-1">{{ $hd }}</div>
                    @endforeach

                    @for($blanks = 1; $blanks < $calendar['firstDay']; $blanks++)
                        <div></div>
                    @endfor

                    @foreach($calendar['days'] as $day)
                        @php
                            $isToday = $day['date'] === now()->format('Y-m-d');
                            $cellBg = $day['alfa'] > 0 ? 'bg-red-50' : ($day['jamaah'] === 5 ? 'bg-emerald-50' : 'bg-white');
                        @endphp
                        <div class="cal-day rounded-lg border {{ $isToday ? 'border-emerald-400 ring-2 ring-emerald-200' : 'border-slate-100' }} {{ $cellBg }} p-1.5 shadow-sm">
                            <div class="text-[10px] font-bold {{ $isToday ? 'text-emerald-600' : 'text-slate-500' }}">{{ $day['day'] }}</div>
                            <div class="grid grid-cols-5 gap-0.5 mt-1">
                                @foreach($calendar['waktu'] as $w)
                                    @php $st = $day['statuses']->get($w)?->status; @endphp
                                    <div class="w-full h-1.5 rounded-full {{ $st === 'Jamaah' ? 'bg-emerald-500' : ($st === 'Masbuq' ? 'bg-orange-500' : ($st === 'Izin' ? 'bg-blue-400' : ($st === 'Alfa' ? 'bg-red-500' : 'bg-slate-200'))) }}"></div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ===== 3. RIWAYAT ===== -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden animate-slide-up">
                <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center shadow-md">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-bold text-slate-800">Riwayat Kehadiran</h3>
                            <p class="text-[11px] text-slate-400">Semua catatan kehadiran Anda</p>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('panel-saya.index') }}" class="flex flex-wrap items-center gap-1.5">
                        <input type="date" name="tanggal" value="{{ $historyTanggal }}" class="border border-slate-300 rounded-lg px-2 py-1.5 text-[12px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                        <select name="waktu" class="border border-slate-300 rounded-lg px-2 py-1.5 text-[12px] text-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                            <option value="all" {{ $waktuFilter === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                            @foreach($calendar['waktu'] as $w)
                                <option value="{{ $w }}" {{ $waktuFilter === $w ? 'selected' : '' }}>{{ $w }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-[12px] font-semibold hover:bg-emerald-600 transition">Filter</button>
                        @if($historyTanggal || $waktuFilter !== 'all')
                            <a href="{{ route('panel-saya.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-[12px] font-semibold hover:bg-slate-200 transition">Reset</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Tanggal</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Waktu</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($history as $p)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-4 py-2.5 text-[12px] text-slate-600">{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-2.5 text-[12px] text-slate-800 font-semibold">{{ $p->waktu_sholat }}</td>
                                    <td class="px-4 py-2.5">
                                        @php
                                            $badge = match($p->status) {
                                                'Jamaah' => 'bg-emerald-100 text-emerald-700',
                                                'Masbuq' => 'bg-orange-100 text-orange-700',
                                                'Izin' => 'bg-blue-100 text-blue-700',
                                                'Alfa' => 'bg-red-100 text-red-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $badge }}">{{ $p->status === 'Jamaah' ? 'Berjamaah' : $p->status }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-[12px] text-slate-500">{{ $p->catatan ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-400 text-sm">Belum ada catatan kehadiran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($history->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100">
                        {{ $history->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
