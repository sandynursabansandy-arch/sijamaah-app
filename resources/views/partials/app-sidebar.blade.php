{{-- Sidebar drawer navigasi (SIAKAD style) + tombol hamburger mengapung --}}
<button type="button" onclick="openAppSidebar()" aria-label="Buka menu navigasi"
        class="fixed top-4 left-4 z-[70] w-11 h-11 rounded-xl bg-white shadow-lg border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-50 hover:text-emerald-600 active:scale-95 transition">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
</button>

{{-- Overlay gelap --}}
<div id="sidebarOverlay" onclick="closeAppSidebar()" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[65]"></div>

{{-- Panel drawer --}}
<aside id="appSidebar" class="fixed top-0 left-0 h-full w-72 max-w-[85vw] bg-white shadow-2xl z-[70] -translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center gap-3 p-4 border-b border-slate-100 shrink-0">
        <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-emerald-200 shrink-0">
            <img src="{{ asset('images/image.png') }}" alt="Logo" class="w-full h-full object-cover">
        </div>
        <div class="min-w-0">
            <p class="text-sm font-extrabold text-slate-800 leading-tight">E-Presensi Sholat</p>
            <p class="text-[11px] text-slate-400">Menu Navigasi</p>
        </div>
        <button type="button" onclick="closeAppSidebar()" aria-label="Tutup menu"
                class="ml-auto w-9 h-9 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto p-3 space-y-1 scrollbar-thin">
        @php
            $navItems = [
                ['Dashboard', 'presensi.index', 'bg-emerald-100 text-emerald-600', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['Rekap Presensi', 'presensi.rekap', 'bg-purple-100 text-purple-600', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['Ranking Berjamaah', 'presensi.rankingBerjamaah', 'bg-amber-100 text-amber-600', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                ['Rekap Berjamaah', 'presensi.rekapBerjamaah', 'bg-teal-100 text-teal-600', 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                ['Ranking Alfa', 'presensi.rankingAlfa', 'bg-red-100 text-red-600', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ];
        @endphp
        @foreach ($navItems as $item)
            @php $active = request()->routeIs($item[1]); @endphp
            <a href="{{ route($item[1]) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $active ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'bg-white/20 text-white' : $item[2] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item[3] }}"/></svg>
                </span>
                {{ $item[0] }}
            </a>
        @endforeach

        @if(auth()->user()?->canManagePresensi())
            @php
                $manageItems = [
                    ['Kelola Santri', 'santri.index', 'bg-blue-100 text-blue-600', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['Input Cepat', 'input-cepat.index', 'bg-orange-100 text-orange-600', 'M13 10V3L4 14h7v7l9-11h-7z'],
                ];
            @endphp
            <div class="my-2 border-t border-dashed border-slate-200"></div>
            @foreach ($manageItems as $item)
                @php $active = request()->routeIs($item[1]); @endphp
                <a href="{{ route($item[1]) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $active ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'bg-white/20 text-white' : $item[2] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item[3] }}"/></svg>
                    </span>
                    {{ $item[0] }}
                </a>
            @endforeach
        @endif

        <div class="my-2 border-t border-dashed border-slate-200"></div>
        @php
            $extraItems = [
                ['Pengajuan Izin', 'izin.index', 'bg-cyan-100 text-cyan-600', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['Riwayat', 'riwayat.index', 'bg-slate-100 text-slate-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
        @endphp
        @foreach ($extraItems as $item)
            @php $active = request()->routeIs($item[1]); @endphp
            <a href="{{ route($item[1]) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $active ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'bg-white/20 text-white' : $item[2] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item[3] }}"/></svg>
                </span>
                {{ $item[0] }}
            </a>
        @endforeach
    </nav>

    <div class="p-3 border-t border-slate-100 shrink-0 space-y-1">
        <a href="{{ route('password.change') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('password.change') ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('password.change') ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            Pengaturan
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" onclick="return confirm('Yakin ingin keluar dari aplikasi?')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </span>
                Keluar
            </button>
        </form>
    </div>
</aside>

<script>
    function openAppSidebar() {
        document.getElementById('appSidebar').classList.remove('-translate-x-full');
        document.getElementById('sidebarOverlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeAppSidebar() {
        document.getElementById('appSidebar').classList.add('-translate-x-full');
        document.getElementById('sidebarOverlay').classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAppSidebar();
    });
</script>
