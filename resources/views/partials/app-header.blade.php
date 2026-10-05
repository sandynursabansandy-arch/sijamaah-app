{{-- ============================================================
     HEADER BERTINGKAT GAYA SIAKAD
     Header 1 : identitas instansi (ikut ter-scroll)
     Header 2 : profil pengguna (sticky top-0) + hamburger
     Menu     : drawer tirai dari kiri di bawah header profil
     ============================================================ --}}

{{-- Ukuran font pengguna: diterapkan ke akar (html) agar memengaruhi seluruh halaman --}}
{{-- ===== HEADER 1: INSTANSI ===== --}}
<header id="app-header" class="bg-white" style="margin:0;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center gap-3">
        <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-full overflow-hidden ring-2 ring-emerald-200 bg-white">
            <img src="{{ asset('images/image.png') }}" alt="Logo Yayasan" class="w-full h-full object-cover">
        </div>
        <div class="min-w-0">
            <p class="text-[10px] sm:text-xs font-medium uppercase tracking-wider text-slate-400">Sistem Informasi Berjamaah</p>
            <h1 class="text-base sm:text-xl font-extrabold text-slate-900 leading-tight truncate">Pondok Pesantren Nurul Wafa</h1>
            <p class="text-[10px] sm:text-xs text-slate-400">Gununghideung, Sukarame</p>
        </div>
    </div>
</header>
<style>
html { margin: 0 !important; padding: 0 !important; }
    body { margin: 0 !important; padding: 0 !important; }
    #app-header { margin-top: 0 !important; border: 0 !important; box-shadow: none !important; }
    html.nav-open { overflow: hidden !important; overflow-x: clip !important; scrollbar-gutter: stable !important; }
    body.nav-open { overflow: hidden !important; overflow-x: clip !important; position: fixed !important; left: 0 !important; right: 0 !important; }
    #navPanel { position: fixed !important; top: 0 !important; left: -300px !important; bottom: 0 !important; width: 280px !important; max-width: 80vw !important; z-index: 2147483647 !important; background: #fff !important; box-shadow: 4px 0 24px rgba(0,0,0,0.25) !important; transition: left 0.4s cubic-bezier(0.4,0,0.2,1) !important; overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; overscroll-behavior: contain !important; }
    #navPanel.nav-open { left: 0 !important; }
    #navOverlay { position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; z-index: 998 !important; background: rgba(0,0,0,0.2) !important; -webkit-backdrop-filter: blur(2px) !important; backdrop-filter: blur(2px) !important; display: none !important; }
    #navOverlay.nav-open { display: block !important; }
    /* Popup notifikasi: selalu melekat di viewport (fixed) agar tidak pernah tertutup konten halaman */
    #notifPanel, #userNotifPanel {
        position: fixed !important;
        top: 9999px !important;
        right: 12px !important;
        left: auto !important;
        max-width: calc(100vw - 24px) !important;
        max-height: calc(100vh - 84px) !important;
        overflow-y: auto !important;
        z-index: 2147483646 !important;
        box-shadow: 0 20px 45px rgba(15,23,42,0.25) !important;
        border-radius: 14px !important;
        margin: 0 !important;
        transform: none !important;
    }
    #notifPanel.open, #userNotifPanel.open { top: 72px !important; }
    #hdrAccountMenu {
        position: fixed !important;
        top: 9999px !important;
        right: 12px !important;
        left: auto !important;
        max-width: calc(100vw - 24px) !important;
        z-index: 2147483646 !important;
        box-shadow: 0 20px 45px rgba(15,23,42,0.25) !important;
        border-radius: 14px !important;
        margin: 0 !important;
        transform: none !important;
    }
    #hdrAccountMenu.open { top: 72px !important; }
    #hdrAccountBackdrop {
        position: fixed !important; inset: 0 !important; z-index: 2147483645 !important;
        background: rgba(0,0,0,0.15) !important; display: none !important;
    }
    #hdrAccountBackdrop.open { display: block !important; }
    #notifBackdrop, #userNotifBackdrop {
        position: fixed !important; inset: 0 !important; z-index: 2147483645 !important;
        background: rgba(0,0,0,0.15) !important; display: none !important;
    }
    #notifBackdrop.open, #userNotifBackdrop.open { display: block !important; }
</style>
@php $userFontSize = auth()->user()?->font_size ?? 'sedang'; @endphp
<script>
    (function () {
        try {
            var size = '{{ $userFontSize }}';
            var root = document.documentElement;
            if (!root) return;
            if (size === 'kecil') root.style.fontSize = '15px';
            else if (size === 'besar') root.style.fontSize = '18px';
            else root.style.fontSize = '16px';
        } catch (e) {}
    })();
</script>

{{-- ===== HEADER 2: PROFIL PENGGUNA (STICKY) ===== --}}
<div id="header2Wrap">
<div id="header2" class="bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center gap-3">

            {{-- Tombol hamburger --}}
            <button type="button" onclick="toggleNavPanel()" aria-label="Buka menu navigasi"
                    id="navPanelBtn"
                    class="shrink-0 w-10 h-10 -ml-1.5 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-teal-600 active:scale-95 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            {{-- Avatar (inisial) + nama & peran --}}
            @php
                $u = auth()->user();
                $jabatanSantri = \App\Models\Santri::where('nama', $u->name)->value('jabatan');
                $roleLabel = $jabatanSantri ?: match ($u->role ?? '') {
                    'admin' => 'Administrator',
                    'musyrif' => 'Musyrif',
                    'pengasuh' => 'Pengasuh',
                    default => ucfirst($u->role ?? 'Pengguna'),
                };
            @endphp
            <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-teal-500 to-teal-600 text-white flex items-center justify-center font-extrabold text-sm ring-2 ring-emerald-100 select-none">
                {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0 leading-tight">
                <p class="text-sm font-bold text-slate-800 truncate">{{ $u->name }}</p>
                <p class="text-[11px] text-slate-400">{{ $roleLabel }}</p>
            </div>

            {{-- Notifikasi (admin) + Dropdown akun di kanan --}}
            @php
                $notifCount = 0;
                if (auth()->user()->isAdmin()) {
                    \App\Support\AdminNotif::ensureTable();
                    $notifCount = \Illuminate\Support\Facades\DB::table('admin_notifications')->where('is_read', false)->count();
                }
            @endphp
            <div class="ml-auto shrink-0 flex items-center" style="gap:2px;">
                @if(auth()->user()->isAdmin())
                <div class="relative shrink-0">
                    <button type="button" onclick="toggleNotifPanel()" aria-label="Notifikasi" id="notifBtn" title="Notifikasi"
                            class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341c-1.704.55-3 2.14-3 3.659v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span id="notifDot" {{ $notifCount > 0 ? '' : 'class="hidden"' }} style="position:absolute; top:6px; right:6px; width:9px; height:9px; border-radius:9999px; background:#ef4444; box-shadow:0 0 0 2px #fff;"></span>
                    </button>
                </div>
                @endif
                @if(!auth()->user()->isAdmin())
                <div class="relative shrink-0">
                    <button type="button" onclick="toggleUserNotifPanel()" aria-label="Notifikasi" id="userNotifBtn" title="Notifikasi"
                            class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341c-1.704.55-3 2.14-3 3.659v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span id="userNotifDot" class="hidden" style="position:absolute; top:6px; right:6px; width:9px; height:9px; border-radius:9999px; background:#ef4444; box-shadow:0 0 0 2px #fff;"></span>
                    </button>
                </div>
                @endif
                <div class="relative shrink-0">
                <button type="button" onclick="toggleHdrAccount()" aria-label="Menu akun" id="hdrAccountBtn"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg id="hdrChevron" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- ===== POPUP NOTIFIKASI (di luar header2 agar tidak ter-clip backdrop-filter) ===== --}}
{{-- Backdrop + menu akun (Profil Saya / Keluar) --}}
<div id="hdrAccountBackdrop" onclick="closeHdrAccount()"></div>
<div id="hdrAccountMenu" class="hidden w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-1">
    <a href="{{ route('profile.show') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Profil Saya
    </a>
    <div class="border-t border-slate-100 my-1"></div>
    <form id="hdrLogoutForm" method="POST" action="{{ route('logout') }}" target="_self">
        @csrf
        <button type="button" onclick="openHdrLogout()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Keluar
        </button>
    </form>
</div>
{{-- Backdrop + panel notifikasi admin --}}
<div id="notifBackdrop" onclick="closeNotifPanel()"></div>
@if(auth()->user()->isAdmin())
<div id="notifPanel" class="hidden bg-white rounded-xl shadow-xl border border-slate-100 py-1" style="width:19rem;">
    <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between" style="gap:8px;">
        <p class="text-sm font-bold text-slate-800">Notifikasi</p>
        <span id="notifMarkAll" onclick="notifMarkAll()" style="display:none; font-size:11px; font-weight:600; color:#059669; cursor:pointer;">Tandai semua dibaca</span>
    </div>
    <div id="notifList" class="overflow-y-auto" style="max-height:19rem;"></div>
    <div id="notifEmpty" class="hidden text-center py-8 px-4 text-xs text-slate-400">Belum ada notifikasi.</div>
</div>
@endif
{{-- Backdrop + panel notifikasi user --}}
<div id="userNotifBackdrop" onclick="closeUserNotifPanel()"></div>
@if(!auth()->user()->isAdmin())
<div id="userNotifPanel" class="hidden bg-white rounded-xl shadow-xl border border-slate-100 py-1" style="width:19rem;">
    <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between" style="gap:8px;">
        <p class="text-sm font-bold text-slate-800">Notifikasi</p>
        <span id="userNotifMarkAll" onclick="userNotifMarkAll()" style="display:none; font-size:11px; font-weight:600; color:#059669; cursor:pointer;">Tandai semua dibaca</span>
    </div>
    <div id="userNotifList" class="overflow-y-auto" style="max-height:19rem;"></div>
    <div id="userNotifEmpty" class="hidden text-center py-8 px-4 text-xs text-slate-400">Tidak ada notifikasi.</div>
</div>
@endif

{{-- ===== DRAWER MENU (tirai penuh dari kiri ke kanan) ===== --}}
<style>
    /* View transition lintas halaman (progressive enhancement, tanpa-JS fallback aman) */
    @view-transition { navigation: auto; }
    ::view-transition-old(root) { animation: 90ms ease-out both fadeAway; }
    ::view-transition-new(root) { animation: 90ms ease-in both fadeIn; }
    @keyframes fadeAway { to { opacity: 0; } }
    @keyframes fadeIn { from { opacity: 0; } }
</style>
<aside id="navPanel" aria-label="Menu navigasi utama">
    <div class="p-3 space-y-1 pt-4">
@php
    $navItems = [
        ['Dashboard', 'presensi.index', 'bg-teal-100 text-teal-600', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['Panel Saya', 'panel-saya.index', 'bg-violet-100 text-violet-600', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['Rekap Berjamaah', 'presensi.rekapBerjamaah', 'bg-teal-100 text-teal-600', 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
        ['Ranking Berjamaah', 'presensi.rankingBerjamaah', 'bg-amber-100 text-amber-600', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
        ['Ranking Alfa', 'presensi.rankingAlfa', 'bg-red-100 text-red-600', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ['Kelola Santri', 'santri.index', 'bg-blue-100 text-blue-600', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['Input Cepat', 'input-cepat.index', 'bg-orange-100 text-orange-600', 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['Kelas & Tahun Ajar', 'kelas.index', 'bg-indigo-100 text-indigo-600', 'M4 6h16M4 12h16M4 18h16M8 12v6'],
        ['Pengajuan Izin', 'izin.index', 'bg-cyan-100 text-cyan-600', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['Riwayat', 'riwayat.index', 'bg-slate-100 text-slate-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Kritik & Saran', auth()->user()->isAdmin() ? 'kritik-saran.index' : 'kritik-saran.create', 'bg-amber-100 text-amber-600', 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ['Kirim Pesan', 'pesan.index', 'bg-cyan-100 text-cyan-600', 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
        ['Kelola User', 'user.index', 'bg-violet-100 text-purple-600', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
    ];
@endphp
@foreach ($navItems as $item)
    @continue(in_array($item[1], ['santri.index', 'kamar.index', 'kelas.index', 'input-cepat.index', 'riwayat.index', 'user.index']) && !auth()->user()?->canManagePresensi())
    @continue(in_array($item[1], ['presensi.rankingBerjamaah', 'presensi.rankingAlfa', 'presensi.rekapBerjamaah']) && !auth()->user()?->canManagePresensi())
    @continue($item[1] === 'user.index' && auth()->user()->role !== 'admin')
    @continue($item[1] === 'panel-saya.index' && auth()->user()?->canManagePresensi())
    @continue($item[1] === 'pesan.index' && !auth()->user()?->canManagePresensi())
    @continue($item[1] === 'kritik-saran.index' && auth()->user()->role !== 'admin')
    @continue($item[1] === 'kritik-saran.create' && auth()->user()->role !== 'pengasuh')
    @php $active = request()->routeIs($item[1]); @endphp
    <a href="{{ route($item[1]) }}" data-turbo="true"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $active ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'bg-white/20 text-white' : $item[2] }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item[3] }}"/></svg>
        </span>
        <span class="truncate">{{ $item[0] }}</span>
    </a>
@endforeach
    </div>
</aside>

{{-- Overlay gelap --}}
<div id="navOverlay" onclick="closeNavPanel()"></div>

{{-- ===== KONFIRMASI KELUAR (global, elegan) ===== --}}
<style>
    #hdrLogoutModal { z-index: 2147483647; }
    #hdrLogoutModal.hdr-lo-show .hdr-lo-backdrop { opacity: 1; }
    #hdrLogoutModal.hdr-lo-show .hdr-lo-box { opacity: 1; transform: translateY(0) scale(1); }
    #hdrLogoutModal .hdr-lo-backdrop { opacity: 0; transition: opacity .22s ease; }
    #hdrLogoutModal .hdr-lo-box {
        opacity: 0; transform: translateY(14px) scale(.96);
        transition: opacity .22s ease, transform .22s cubic-bezier(.21,1.02,.73,1);
    }
    #hdrLogoutModal .hdr-lo-ring { box-shadow: 0 10px 30px -8px rgba(220,38,38,.45); }
</style>
<div id="hdrLogoutModal" class="fixed inset-0 hidden items-center justify-center px-4" role="dialog" aria-modal="true" aria-labelledby="hdrLogoutTitle">
    <div class="hdr-lo-backdrop absolute inset-0 bg-slate-900/45 backdrop-blur-sm" onclick="closeHdrLogout()"></div>
    <div class="hdr-lo-box relative w-full max-w-sm rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="px-6 pt-6 pb-5 text-center">
            <div class="hdr-lo-ring w-14 h-14 mx-auto rounded-full bg-red-50 text-red-500 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>
            <h3 id="hdrLogoutTitle" class="mt-4 text-base font-bold text-slate-800">Keluar dari aplikasi?</h3>
            <p class="mt-1.5 text-[13px] leading-relaxed text-slate-500">
                Anda akan keluar dari sesi <span class="font-semibold text-slate-700">SANTRI.ID</span>. Lanjutkan?
            </p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-6">
            <button type="button" onclick="closeHdrLogout()" id="hdrLogoutCancel"
                class="flex-1 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition">Batal</button>
            <button type="button" onclick="confirmHdrLogout()" id="hdrLogoutOk"
                class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 text-white text-sm font-semibold shadow hover:bg-red-600 transition">Ya, Keluar</button>
        </div>
    </div>
</div>

<script>
    (function () {
        // Konfirmasi keluar: modal halus, tutup via Escape/backdrop, fokus ke tombol utama.
        var m = document.getElementById('hdrLogoutModal');
        var f = document.getElementById('hdrLogoutForm');
        if (!m || !f) return;
        var terakhir = null;

        window.openHdrLogout = function () {
            terakhir = document.activeElement;
            if (typeof closeHdrAccount === 'function') closeHdrAccount();
            m.classList.remove('hidden');
            m.classList.add('flex');
            requestAnimationFrame(function () { m.classList.add('hdr-lo-show'); });
            var ok = document.getElementById('hdrLogoutOk');
            if (ok) setTimeout(function () { ok.focus(); }, 60);
        };

        window.closeHdrLogout = function () {
            if (m.classList.contains('hidden')) return;
            m.classList.remove('hdr-lo-show');
            setTimeout(function () {
                m.classList.add('hidden');
                m.classList.remove('flex');
            }, 200);
            if (terakhir && typeof terakhir.focus === 'function') terakhir.focus();
        };

        window.confirmHdrLogout = function () {
            var ok = document.getElementById('hdrLogoutOk');
            if (ok) { ok.disabled = true; ok.textContent = 'Keluar...'; }
            f.submit();
        };

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !m.classList.contains('hidden')) closeHdrLogout();
        });
    })();

    (function () {
        var h1 = document.getElementById('app-header');
        var h2 = document.getElementById('header2');
        var h2w = document.getElementById('header2Wrap');
        if (!h1 || !h2) return;
        if (h2w) h2w.style.height = h2.offsetHeight + 'px';
        function sizeWrap() { if (h2w && h2.offsetHeight) h2w.style.height = h2.offsetHeight + 'px'; }
        window.addEventListener('load', sizeWrap);
        function stickHeader2() {
            var h1Bottom = h1.getBoundingClientRect().bottom;
            if (h1Bottom <= 0) {
                h2.style.position = 'fixed';
                h2.style.top = '0';
                h2.style.left = '0';
                h2.style.right = '0';
                h2.style.zIndex = '999';
                h2.style.width = '100%';
            } else {
                h2.style.position = '';
                h2.style.top = '';
                h2.style.left = '';
                h2.style.right = '';
                h2.style.zIndex = '';
                h2.style.width = '';
            }
        }
        window.addEventListener('scroll', stickHeader2, { passive: true });
        stickHeader2();
    })();
    var _navScrollY = 0;
    function _updateNavTop() {
        var panel = document.getElementById('navPanel');
        var overlay = document.getElementById('navOverlay');
        var h2 = document.getElementById('header2');
        if (!h2) return;
        var rect = h2.getBoundingClientRect();
        var top;
        if (rect.height > 0) {
            top = rect.bottom;
        } else {
            top = h2.offsetHeight;
        }
        if (panel) panel.style.setProperty('top', top + 'px', 'important');
        if (overlay) overlay.style.setProperty('top', top + 'px', 'important');
    }
    function lockScrollbarSpace() {}
    function unlockScrollbarSpace() {}
    function openNavPanel() {
        _navScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
        _updateNavTop();
        document.body.style.top = '-' + _navScrollY + 'px';
        lockScrollbarSpace();
        var panel = document.getElementById('navPanel');
        if (!panel) return;
        panel.classList.add('nav-open');
        var overlay = document.getElementById('navOverlay');
        if (overlay) overlay.classList.add('nav-open');
        document.body.classList.add('nav-open');
        document.documentElement.classList.add('nav-open');
        closeHdrAccount();
        closeNotifPanel();
        closeUserNotifPanel();
    }
    function closeNavPanel() {
        var panel = document.getElementById('navPanel');
        if (!panel) return;
        panel.classList.remove('nav-open');
        var overlay = document.getElementById('navOverlay');
        if (overlay) overlay.classList.remove('nav-open');
        document.body.classList.remove('nav-open');
        document.documentElement.classList.remove('nav-open');
        unlockScrollbarSpace();
        document.body.style.top = '';
        window.scrollTo(0, _navScrollY);
    }
    function toggleNavPanel() {
        var panel = document.getElementById('navPanel');
        if (!panel) return;
        if (panel.classList.contains('nav-open')) { closeNavPanel(); } else { openNavPanel(); }
    }
    function toggleHdrAccount() {
        var m = document.getElementById('hdrAccountMenu');
        var c = document.getElementById('hdrChevron');
        if (m.classList.contains('hidden')) {
            lockNotifScroll();
            closeNotifPanel();
            closeUserNotifPanel();
            m.classList.remove('hidden');
            m.classList.add('open');
            positionNotif('hdrAccountMenu');
            var bd = document.getElementById('hdrAccountBackdrop');
            if (bd) bd.classList.add('open');
            requestAnimationFrame(function () { m.classList.remove('scale-95', 'opacity-0'); });
            c.classList.add('rotate-180');
        } else { closeHdrAccount(); }
    }
    function closeHdrAccount() {
        var m = document.getElementById('hdrAccountMenu');
        if (!m || m.classList.contains('hidden')) return;
        m.classList.add('scale-95', 'opacity-0');
        setTimeout(function () { m.classList.add('hidden'); }, 150);
        var bd = document.getElementById('hdrAccountBackdrop');
        if (bd) bd.classList.remove('open');
        var c = document.getElementById('hdrChevron');
        if (c) c.classList.remove('rotate-180');
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeNavPanel(); closeHdrAccount(); closeNotifPanel(); closeUserNotifPanel(); }
    });
    document.addEventListener('click', function (e) {
        var menu = document.getElementById('hdrAccountMenu');
        var btn = document.getElementById('hdrAccountBtn');
        if (menu && !menu.classList.contains('hidden') && !menu.contains(e.target) && !btn.contains(e.target)) closeHdrAccount();
        var np = document.getElementById('notifPanel');
        var nbtn = document.getElementById('notifBtn');
        if (np && !np.classList.contains('hidden') && !np.contains(e.target) && !nbtn.contains(e.target)) closeNotifPanel();
        var unp = document.getElementById('userNotifPanel');
        var unbtn = document.getElementById('userNotifBtn');
        if (unp && !unp.classList.contains('hidden') && !unp.contains(e.target) && !unbtn.contains(e.target)) closeUserNotifPanel();
    });

    function notifBadge() {
        return fetch('/notifikasi/jumlah', {headers: {'Accept':'application/json'}})
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var dot = document.getElementById('notifDot');
                if (dot) { if (d.count > 0) { dot.classList.remove('hidden'); } else { dot.classList.add('hidden'); } }
            })
            .catch(function () {});
    }
    function notifTime(ts) {
        var d = new Date(String(ts || '').replace(' ', 'T'));
        if (isNaN(d)) return '';
        var s = (Date.now() - d.getTime()) / 1000;
        if (s < 60) return 'baru saja';
        if (s < 3600) return Math.floor(s / 60) + ' mnt lalu';
        if (s < 86400) return Math.floor(s / 3600) + ' jam lalu';
        return Math.floor(s / 86400) + ' hari lalu';
    }
    function notifLoad() {
        fetch('/notifikasi/daftar', {headers: {'Accept':'application/json'}})
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var list = document.getElementById('notifList');
                var empty = document.getElementById('notifEmpty');
                var markAll = document.getElementById('notifMarkAll');
                if (!list) return;
                var items = d.items || [];
                list.innerHTML = '';
                if (!items.length) {
                    empty.classList.remove('hidden');
                    if (markAll) markAll.style.display = 'none';
                    notifBadge();
                    return;
                }
                empty.classList.add('hidden');
                var unread = items.filter(function (i) { return !i.is_read; }).length;
                if (markAll) markAll.style.display = unread > 0 ? '' : 'none';
                items.forEach(function (it) {
                    var a = document.createElement('a');
                    a.href = it.url;
                    a.className = 'flex items-start px-4 py-3 hover:bg-slate-50 transition-colors ' + (it.is_read ? '' : 'bg-emerald-50');
                    a.innerHTML =
                        '<span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 ' + (it.type === 'izin' ? 'bg-cyan-100 text-cyan-600' : 'bg-amber-100 text-amber-600') + '">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>' +
                        '<span class="min-w-0 flex-1" style="min-width:0;">' +
                        '<span class="block text-xs font-medium ' + (it.is_read ? 'text-slate-500' : 'text-slate-800') + '" style="word-wrap:break-word;">' + it.title + '</span>' +
                        '<span class="block text-[10px] text-slate-400" style="margin-top:2px;">' + notifTime(it.created_at) + '</span></span>' +
                        (it.is_read ? '' : '<span class="mt-1 w-2 h-2 rounded-full shrink-0" style="background:#f59e0b; margin-top:4px;"></span>');
                    a.addEventListener('click', function (ev) {
                        ev.preventDefault();
                        var href = a.getAttribute('href') || it.url || '#';
                        var dot = document.getElementById('notifDot');
                        if (dot) dot.classList.add('hidden');
                        notifMarkOne(it.id);
                        notifBadge();
                        closeNotifPanel();
                        window.location.href = href;
                    });
                    list.appendChild(a);
                });
                notifBadge();
            })
            .catch(function () {});
    }
    function notifMarkOne(id) {
        fetch('/notifikasi/' + id + '/baca', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})
            .then(function () { notifBadge(); notifLoad(); })
            .catch(function () {});
    }
    function notifMarkAll() {
        fetch('/notifikasi/baca-semua', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})
            .then(function () { notifLoad(); })
            .catch(function () {});
    }
    function toggleNotifPanel() {
        var p = document.getElementById('notifPanel');
        if (!p) return;
        if (p.classList.contains('hidden')) {
            lockNotifScroll();
            closeHdrAccount();
            closeUserNotifPanel();
            p.classList.remove('hidden');
            p.classList.add('open');
            positionNotif('notifPanel');
            var bd = document.getElementById('notifBackdrop');
            if (bd) bd.classList.add('open');
            notifLoad();
        } else { closeNotifPanel(); }
    }
    function closeNotifPanel() {
        var p = document.getElementById('notifPanel');
        if (!p || p.classList.contains('hidden')) return;
        p.classList.add('hidden');
        p.classList.remove('open');
        var bd = document.getElementById('notifBackdrop');
        if (bd) bd.classList.remove('open');
        unlockNotifScroll();
    }
    function appBadge(count) {
        try {
            if (count > 0) { if (navigator.setAppBadge) navigator.setAppBadge(count); }
            else { if (navigator.clearAppBadge) navigator.clearAppBadge(); }
        } catch (err) {}
    }
    function escHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function escBadge() {
        return fetch('/pesan-user/jumlah', {headers: {'Accept':'application/json'}})
            .then(function (r) { return r.json(); })
            .catch(function () { return {count: 0}; })
            .then(function (d) {
                var total = (d.count || 0);
                var dot = document.getElementById('userNotifDot');
                if (dot) { if (total > 0) { dot.classList.remove('hidden'); } else { dot.classList.add('hidden'); } }
                appBadge(total);
            });
    }
    function renderUserNotifItem(it) {
        var a = document.createElement('a');
        a.href = it.url || '#';
        a.className = 'flex items-start px-4 py-3 hover:bg-slate-50 transition-colors ' + (it.is_read ? '' : 'bg-emerald-50');
        var cls, icon;
        if (it.type === 'pesan') { cls = 'bg-cyan-100 text-cyan-600'; icon = 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'; }
        else if (it.type === 'danger') { cls = 'bg-red-100 text-red-600'; icon = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'; }
        else { cls = 'bg-teal-100 text-teal-600'; icon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'; }
        a.innerHTML =
            '<span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 ' + cls + '">' +
            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + icon + '"/></svg></span>' +
            '<span class="min-w-0 flex-1" style="min-width:0;">' +
            '<span class="block text-xs font-bold ' + (it.is_read ? 'text-slate-600' : 'text-slate-800') + '">' + escHtml(it.title) + '</span>' +
            '<span class="block text-[10px] text-slate-500" style="margin-top:2px; word-wrap:break-word;">' + escHtml(it.text) + '</span>' +
            (it.created_at ? '<span class="block text-[10px] text-slate-400" style="margin-top:2px;">' + notifTime(it.created_at) + '</span>' : '') +
            '</span>' +
            (it.is_read ? '' : '<span class="mt-1 w-2 h-2 rounded-full shrink-0" style="background:#f59e0b; margin-top:6px;"></span>');
        a.addEventListener('click', function (ev) {
            ev.preventDefault();
            var href = a.getAttribute('href') || it.url || '#';
            var dot = document.getElementById('userNotifDot');
            if (dot) dot.classList.add('hidden');
            closeUserNotifPanel();
            if (it.type === 'pesan' && !it.is_read && it.id) {
                fetch('/pesan-user/' + it.id + '/baca', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})
                    .then(function () { escBadge(); })
                    .catch(function () {});
            }
            window.location.href = href;
        });
        return a;
    }
    function userNotifMarkAll() {
        fetch('/pesan-user/baca-semua', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})
            .then(function () { escBadge(); userNotifLoad(); })
            .catch(function () {});
    }
    function userNotifLoad() {
        return Promise.all([
            fetch('/notifikasi-user/daftar', {headers: {'Accept':'application/json'}}).then(function (r) { return r.json(); }).catch(function () { return {items: []}; }),
            fetch('/pesan-user/daftar', {headers: {'Accept':'application/json'}}).then(function (r) { return r.json(); }).catch(function () { return {items: []}; })
        ]).then(function (res) {
            var list = document.getElementById('userNotifList');
            var empty = document.getElementById('userNotifEmpty');
            var markAll = document.getElementById('userNotifMarkAll');
            if (!list) return;
            var items = (res[0].items || []).concat((res[1].items || []));
            list.innerHTML = '';
            if (!items.length) {
                if (empty) empty.classList.remove('hidden');
                if (markAll) markAll.style.display = 'none';
                escBadge();
                return;
            }
            if (empty) empty.classList.add('hidden');
            var unread = items.filter(function (i) { return !i.is_read; }).length;
            if (markAll) markAll.style.display = unread > 0 ? '' : 'none';
            items.forEach(function (it) { list.appendChild(renderUserNotifItem(it)); });
            escBadge();
        }).catch(function () {});
    }
    function toggleUserNotifPanel() {
        var p = document.getElementById('userNotifPanel');
        if (!p) return;
        if (p.classList.contains('hidden')) {
            lockNotifScroll();
            closeHdrAccount();
            closeNotifPanel();
            p.classList.remove('hidden');
            p.classList.add('open');
            positionNotif('userNotifPanel');
            var bd = document.getElementById('userNotifBackdrop');
            if (bd) bd.classList.add('open');
            userNotifLoad();
        } else { closeUserNotifPanel(); }
    }
    function closeUserNotifPanel() {
        var p = document.getElementById('userNotifPanel');
        if (!p || p.classList.contains('hidden')) return;
        p.classList.add('hidden');
        p.classList.remove('open');
        var bd = document.getElementById('userNotifBackdrop');
        if (bd) bd.classList.remove('open');
        if (document.getElementById('notifPanel') && document.getElementById('notifPanel').classList.contains('hidden')) unlockNotifScroll();
    }
    function lockNotifScroll() {
        document.querySelectorAll('#navPanel.nav-open, #navOverlay.nav-open').forEach(function (el) { el.classList.remove('nav-open'); });
        document.body.classList.remove('nav-open');
        document.documentElement.classList.remove('nav-open');
        unlockScrollbarSpace();
        document.body.style.top = '';
    }
    function unlockNotifScroll() {}
    function positionNotif(id) {
        var h2 = document.getElementById('header2');
        var p = document.getElementById(id);
        if (!h2 || !p) return;
        var top = h2.getBoundingClientRect().bottom + 8;
        p.style.setProperty('top', top + 'px', 'important');
    }
    function positionOpenNotif() {
        var nb = document.getElementById('notifPanel');
        if (nb && !nb.classList.contains('hidden')) positionNotif('notifPanel');
        var ub = document.getElementById('userNotifPanel');
        if (ub && !ub.classList.contains('hidden')) positionNotif('userNotifPanel');
        var ab = document.getElementById('hdrAccountMenu');
        if (ab && !ab.classList.contains('hidden')) positionNotif('hdrAccountMenu');
    }
    var _nrAF = null;
    window.addEventListener('scroll', function () {
        if (_nrAF) return;
        _nrAF = requestAnimationFrame(function () { _nrAF = null; positionOpenNotif(); });
    }, { passive: true });
    window.addEventListener('resize', positionOpenNotif);
    // Polling badge notifikasi: jeda 2 menit, berhenti saat tab tidak terlihat,
    // dan langsung cek lagi begitu tab aktif kembali. Interval 30 detik lebih sering
    // hanya menambah beban request pada shared hosting tanpa manfaat nyata.
    (function () {
        var POLL_MS = 120000;
        var timer = null;
        var pending = false;
        var fns = [];
        if (document.getElementById('userNotifPanel')) fns.push(escBadge);
        if (document.getElementById('notifPanel')) fns.push(notifBadge);
        if (!fns.length) return;

        function tick() {
            if (document.hidden || pending) return;
            pending = true;
            Promise.all(fns.map(function (f) {
                try { return f(); } catch (e) { return null; }
            })).catch(function () {}).finally(function () { pending = false; });
        }

        fns.forEach(function (f) { try { f(); } catch (e) {} });

        function start() { if (!timer) timer = setInterval(tick, POLL_MS); }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) { stop(); } else { tick(); start(); }
        });
        start();
    })();
</script>
