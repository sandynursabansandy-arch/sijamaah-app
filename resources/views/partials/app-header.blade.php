{{-- ============================================================
     HEADER BERTINGKAT GAYA SIAKAD
     Header 1 : identitas instansi (ikut ter-scroll)
     Header 2 : profil pengguna (sticky top-0) + hamburger
     Menu     : drawer tirai dari kiri di bawah header profil
     ============================================================ --}}

{{-- ===== HEADER 1: INSTANSI ===== --}}
<header class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3">
        <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-full overflow-hidden ring-2 ring-emerald-200 bg-white">
            <img src="{{ asset('images/image.png') }}" alt="Logo Yayasan" class="w-full h-full object-cover">
        </div>
        <div class="min-w-0">
            <p class="text-[10px] sm:text-xs font-medium uppercase tracking-wider text-slate-400">Sistem Informasi Berjamaah</p>
            <h1 class="text-base sm:text-xl font-extrabold text-slate-900 leading-tight truncate">Pondok Pesantren Nurul Wafa</h1>
        </div>
    </div>
</header>

{{-- ===== HEADER 2: PROFIL PENGGUNA (STICKY) ===== --}}
<div class="sticky top-0 z-[999] bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center gap-3">

            {{-- Tombol hamburger --}}
            <button type="button" onclick="toggleNavPanel()" aria-label="Buka menu navigasi"
                    id="navPanelBtn"
                    class="shrink-0 w-10 h-10 -ml-1.5 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-emerald-600 active:scale-95 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            {{-- Avatar (inisial) + nama & peran --}}
            @php
                $u = auth()->user();
                $roleLabel = match ($u->role ?? '') {
                    'admin' => 'Administrator',
                    'musyrif' => 'Musyrif',
                    'pengasuh' => 'Pengasuh',
                    default => ucfirst($u->role ?? 'Pengguna'),
                };
            @endphp
            <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-extrabold text-sm ring-2 ring-emerald-100 select-none">
                {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0 leading-tight">
                <p class="text-sm font-bold text-slate-800 truncate">{{ $u->name }}</p>
                <p class="text-[11px] text-slate-400">{{ $roleLabel }}</p>
            </div>

            {{-- Dropdown akun di kanan (panah) --}}
            <div class="relative ml-auto shrink-0">
                <button type="button" onclick="toggleHdrAccount()" aria-label="Menu akun" id="hdrAccountBtn"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg id="hdrChevron" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="hdrAccountMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-[999]">
                    <a href="{{ route('password.change') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Ganti Password
                    </a>
                    <div class="border-t border-slate-100 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Yakin ingin keluar dari aplikasi?')" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== DRAWER MENU (tirai dari kiri, menempel tepat di bawah Header 2) ===== --}}
    <aside id="navPanel" aria-label="Menu navigasi utama"
           class="absolute top-full left-0 z-[998] w-1/2 min-w-[240px] max-w-[300px] h-[calc(100vh-5rem)] bg-white shadow-2xl -translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto scrollbar-thin">
    <div class="p-3 space-y-1">
        @php
            $navItems = [
                ['Dashboard', 'presensi.index', 'bg-emerald-100 text-emerald-600', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['Rekap Presensi', 'presensi.rekap', 'bg-purple-100 text-purple-600', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['Ranking Berjamaah', 'presensi.rankingBerjamaah', 'bg-amber-100 text-amber-600', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                ['Rekap Berjamaah', 'presensi.rekapBerjamaah', 'bg-teal-100 text-teal-600', 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                ['Ranking Alfa', 'presensi.rankingAlfa', 'bg-red-100 text-red-600', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                ['Kelola Santri', 'santri.index', 'bg-blue-100 text-blue-600', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                ['Input Cepat', 'input-cepat.index', 'bg-orange-100 text-orange-600', 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['Pengajuan Izin', 'izin.index', 'bg-cyan-100 text-cyan-600', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['Riwayat', 'riwayat.index', 'bg-slate-100 text-slate-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Pengaturan', 'password.change', 'bg-indigo-100 text-indigo-600', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            ];
        @endphp
        @foreach ($navItems as $item)
            @continue(in_array($item[1], ['santri.index', 'input-cepat.index']) && !auth()->user()?->canManagePresensi())
            @php $active = request()->routeIs($item[1]); @endphp
            <a href="{{ route($item[1]) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $active ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'bg-white/20 text-white' : $item[2] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item[3] }}"/></svg>
                </span>
                <span class="truncate">{{ $item[0] }}</span>
            </a>
        @endforeach
    </div>
    </aside>

    {{-- Overlay gelap: hanya area di bawah Header 2 agar header tetap terlihat --}}
    <div id="navOverlay" onclick="closeNavPanel()" class="hidden absolute top-full inset-x-0 z-[997] h-[calc(100vh-5rem)] bg-black/50 backdrop-blur-sm"></div>
</div>

<script>
    function openNavPanel() {
        document.getElementById('navPanel').classList.remove('-translate-x-full');
        document.getElementById('navOverlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        closeHdrAccount();
    }
    function closeNavPanel() {
        var p = document.getElementById('navPanel');
        if (p) p.classList.add('-translate-x-full');
        var o = document.getElementById('navOverlay');
        if (o) o.classList.add('hidden');
        document.body.style.overflow = '';
    }
    function toggleNavPanel() {
        var p = document.getElementById('navPanel');
        if (p.classList.contains('-translate-x-full')) { openNavPanel(); } else { closeNavPanel(); }
    }
    function toggleHdrAccount() {
        var m = document.getElementById('hdrAccountMenu');
        var c = document.getElementById('hdrChevron');
        if (m.classList.contains('hidden')) {
            m.classList.remove('hidden');
            requestAnimationFrame(function () { m.classList.remove('scale-95', 'opacity-0'); });
            c.classList.add('rotate-180');
        } else { closeHdrAccount(); }
    }
    function closeHdrAccount() {
        var m = document.getElementById('hdrAccountMenu');
        if (!m || m.classList.contains('hidden')) return;
        m.classList.add('scale-95', 'opacity-0');
        setTimeout(function () { m.classList.add('hidden'); }, 150);
        var c = document.getElementById('hdrChevron');
        if (c) c.classList.remove('rotate-180');
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeNavPanel(); closeHdrAccount(); }
    });
    document.addEventListener('click', function (e) {
        var menu = document.getElementById('hdrAccountMenu');
        var btn = document.getElementById('hdrAccountBtn');
        if (menu && !menu.classList.contains('hidden') && !menu.contains(e.target) && !btn.contains(e.target)) closeHdrAccount();
    });
</script>
