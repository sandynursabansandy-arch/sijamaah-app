<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login � SIJAMAAH</title>
    @vite(['resources/css/app.css'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * { font-family: 'Inter', system-ui, sans-serif; }

        /* === ANIMATED GRADIENT BACKGROUND === */
        .bg-animated {
            background: linear-gradient(-45deg, #064e3b, #065f46, #047857, #059669, #10b981, #34d399, #065f46, #064e3b);
            background-size: 400% 400%;
            animation: gradientShift 12s ease infinite;
        }
        @keyframes gradientShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* === FLOATING PARTICLES === */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            animation: floatUp linear infinite;
        }
        @keyframes floatUp {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        /* === GLASS CARD === */
        .glass-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 25px 60px rgba(0,0,0,0.15), 0 0 40px rgba(16,185,129,0.08);
        }

        /* === LOGO PULSE === */
        .logo-pulse {
            animation: logoPulse 3s ease-in-out infinite;
        }
        @keyframes logoPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            50%      { transform: scale(1.03); box-shadow: 0 0 30px 10px rgba(16,185,129,0.15); }
        }

        /* === INPUT GLOW ON FOCUS === */
        .input-glow {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
        }
        .input-glow:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.1), 0 4px 12px rgba(16,185,129,0.08);
        }

        /* === BUTTON GRADIENT SHIFT === */
        .btn-login {
            background: linear-gradient(135deg, #059669, #10b981, #34d399);
            background-size: 200% 200%;
            animation: btnGradient 3s ease infinite;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16,185,129,0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        @keyframes btnGradient {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* === SLIDE-IN ANIMATIONS === */
        .anim-slide-left {
            animation: slideLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes slideLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .anim-slide-right {
            animation: slideRight 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
        }
        @keyframes slideRight {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .anim-fade-up {
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* === ORNAMENT PATTERN === */
        .ornament-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* === PASSWORD TOGGLE === */
        .toggle-pass {
            transition: all 0.2s;
        }
        .toggle-pass:hover {
            color: #10b981;
        }

        /* === LOADING SPINNER === */
        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.7s linear infinite;
            display: none;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* === RIPPLE EFFECT === */
        .ripple {
            position: relative;
            overflow: hidden;
        }
        .ripple::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 10%, transparent 10.01%);
            transform: scale(10);
            opacity: 0;
            transition: transform 0.5s, opacity 1s;
        }
        .ripple:active::after {
            transform: scale(0);
            opacity: 0.3;
            transition: 0s;
        }

        /* === TYPING ANIMATION === */
        .type-title {
            display: inline-block;
            position: relative;
            white-space: nowrap;
        }
        .typing-cursor::after {
            content: '|';
            color: #5eead4;
            animation: blink 0.7s step-end infinite;
            font-weight: 300;
            opacity: 0.9;
        }
        .type-sub {
            display: block;
            position: relative;
            white-space: nowrap;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0; }
        }
        @keyframes blinkCursor {
            from, to { border-color: rgba(255,255,255,0.7); }
            50%      { border-color: transparent; }
        }
    </style>
</head>
<body class="min-h-screen bg-animated ornament-pattern overflow-x-hidden">

    <!-- Floating Particles -->
    <div id="particles" class="fixed inset-0 pointer-events-none z-0"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">

        <!-- ======= DESKTOP: Split Layout ======= -->
        <div class="hidden lg:flex w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden anim-slide-left" style="min-height: 540px;">

            <!-- Left Panel: Branding -->
            <div class="w-[45%] relative flex flex-col items-center justify-center p-8 sm:p-10 pt-2 pb-12 text-center rounded-[2rem] left-panel" style="background: linear-gradient(135deg, #064e3b, #065f46, #047857);">

                <!-- Typing Text -->
                <div class="mb-5" style="min-height: 60px;">
                    <h1 class="text-4xl font-extrabold tracking-tight mb-1 type-title text-center"><span id="tTitle" style="color: #fbbf24;"></span></h1>
                    <p class="text-sm font-medium type-sub text-center"><span id="tSub" style="color: #5eead4;"></span><span class="typing-cursor" id="cursorSub"></span></p>
                </div>

                <!-- Logo -->
                <div class="logo-pulse w-[85%] aspect-square max-w-[300px] rounded-2xl flex items-center justify-center shadow-xl" style="filter: drop-shadow(0 0 20px rgba(255,255,255,0.25)) drop-shadow(0 0 40px rgba(255,255,255,0.15));">
                    <img src="{{ asset('images/nw.png') }}" alt="Logo" class="w-full h-full object-contain drop-shadow-lg">
                </div>
            </div>

            <!-- Right Panel: Login Form -->
            <div class="w-[55%] bg-white/95 backdrop-blur-xl flex items-center justify-center p-10 anim-slide-right">
                <div class="w-full max-w-[360px]">
                    <div class="mb-8">
                        <h2 class="text-2xl font-extrabold text-slate-800 mb-1">Selamat Datang</h2>
                        <p class="text-slate-500 text-sm">Masuk ke akun Anda untuk melanjutkan</p>
                    </div>

                    <!-- Error Message -->
                    @if($errors->any())
                        <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-start gap-2 anim-fade-up">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-5" autocomplete="off">
                        @csrf

                        <!-- Email -->
                        <div class="anim-fade-up" style="animation-delay:0.1s">
                            <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Email</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                </span>
                                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="off"
                                    class="input-glow w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none transition-smooth"
                                    placeholder="admin@sijamaah.id">
                            </div>
                        </div>

                        <!-- Cari Akun: temukan email milik anda -->
                        <div class="anim-fade-up" style="animation-delay:0.15s">
                            <button type="button" id="btnCariAkun"
                                class="flex items-center cursor-pointer text-xs font-semibold text-teal-600 transition"
                                style="background:transparent;border:none;padding:0;gap:6px">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                                <span>Lupa email? Cari akun Anda</span>
                                <svg id="chevCari" class="w-4 h-4" style="transition:transform .2s" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div id="panelCariAkun" class="hidden mt-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <input type="text" id="cariNama" autocomplete="off" spellcheck="false"
                                    class="input-glow w-full px-3 py-2 rounded-lg bg-white text-slate-800 text-sm focus:outline-none"
                                    placeholder="Ketik sebagian nama Anda, contoh: tris sandi">
                                <p id="cariCatatan" class="hidden mt-1 text-[10px] text-slate-400">Klik akun milik Anda untuk mengisi email di atas.</p>
                                <div id="hasilCari" class="mt-1"></div>
                                <p id="cariLoading" class="hidden mt-1 text-xs text-slate-400">Mencari akun...</p>
                                <p id="cariKosong" class="hidden mt-1 text-xs text-slate-400">Tidak ada akun yang cocok.</p>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="anim-fade-up" style="animation-delay:0.2s">
                            <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Password</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input type="password" name="password" id="password" required autocomplete="new-password"
                                    class="input-glow w-full pl-10 pr-10 py-3 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none transition-smooth"
                                    placeholder="Masukkan password">
                                <button type="button" onclick="togglePassword()" class="toggle-pass absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg id="eyeOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg id="eyeClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="anim-fade-up" style="animation-delay:0.3s">
                            <button type="submit" id="btnLogin"
                                class="btn-login ripple w-full py-3 rounded-xl text-white font-bold text-sm tracking-wide flex items-center justify-center gap-2">
                                <span id="btnText">Masuk</span>
                                <div id="btnSpinner" class="spinner"></div>
                                <svg id="btnArrow" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>

                    <!-- Footer -->
                    <div class="mt-6 text-center anim-fade-up" style="animation-delay:0.4s">
                        <p class="text-sm text-slate-500">
                            Belum punya akun?
                            <a href="{{ route('register') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">Daftar Akun</a>
                        </p>
                    </div>
                    <div class="mt-3 text-center anim-fade-up" style="animation-delay:0.45s">
                        <p class="text-[10px] text-slate-400 mt-2">&copy; {{ date('Y') }} SIJAMAAH &middot; Pesantren Digital</p>
                        <a href="javascript:void(0)" id="btnInstallPwaD" class="hidden mt-2 inline-flex items-center justify-center gap-1 text-[11px] font-semibold text-emerald-600">Pasang Aplikasi</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======= MOBILE: Full Card ======= -->
        <div class="lg:hidden w-full max-w-sm anim-slide-right">
            <div class="glass-card rounded-3xl p-8 text-center">
                <!-- Logo -->
                <div class="logo-pulse w-40 h-40 sm:w-60 sm:h-60 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-800 flex items-center justify-center mx-auto mb-4 shadow-xl p-4">
                    <img src="{{ asset('images/nw.png') }}" alt="Logo" class="w-full h-full object-contain drop-shadow-lg">
                </div>
                <div style="min-height: 55px;" class="mb-1">
                    <h2 class="text-2xl font-extrabold tracking-tight type-title text-center"><span id="tTitleM" style="color: #059669;"></span><span class="typing-cursor" id="cursorTitleM" style="display:none;"></span></h2>
                    <p class="text-xs font-semibold type-sub text-center"><span id="tSubM" style="color: #0d9488;"></span><span class="typing-cursor" id="cursorSubM" style="display:none;"></span></p>
                </div>
                <p class="text-slate-500 text-xs mb-6">Masuk ke akun Anda untuk melanjutkan</p>

                <!-- Error Message -->
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs flex items-start gap-2 anim-fade-up">
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginFormMobile" class="space-y-4 text-left" autocomplete="off">
                    @csrf

                    <div class="anim-fade-up" style="animation-delay:0.2s">
                        <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1 pl-0.5">Email</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="off"
                                class="input-glow w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                placeholder="admin@sijamaah.id">
                        </div>
                    </div>

                    <div class="anim-fade-up" style="animation-delay:0.25s">
                        <button type="button" id="btnCariAkunM"
                            class="flex items-center cursor-pointer text-xs font-semibold text-teal-600 transition"
                            style="background:transparent;border:none;padding:0;gap:6px">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                            <span>Lupa email? Cari akun Anda</span>
                            <svg id="chevCariM" class="w-4 h-4" style="transition:transform .2s" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div id="panelCariAkunM" class="hidden mt-2 rounded-xl border border-slate-100 bg-slate-50 p-3 text-left">
                            <input type="text" id="cariNamaM" autocomplete="off" spellcheck="false"
                                class="input-glow w-full px-3 py-2 rounded-lg bg-white text-slate-800 text-sm focus:outline-none"
                                placeholder="Ketik sebagian nama Anda, contoh: tris sandi">
                            <p id="cariCatatanM" class="hidden mt-1 text-[10px] text-slate-400">Klik akun milik Anda untuk mengisi email di atas.</p>
                            <div id="hasilCariM" class="mt-1"></div>
                            <p id="cariLoadingM" class="hidden mt-1 text-xs text-slate-400">Mencari akun...</p>
                            <p id="cariKosongM" class="hidden mt-1 text-xs text-slate-400">Tidak ada akun yang cocok.</p>
                        </div>
                    </div>

                    <div class="anim-fade-up" style="animation-delay:0.3s">
                        <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1 pl-0.5">Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <input type="password" name="password" id="passwordMobile" required autocomplete="new-password"
                                class="input-glow w-full pl-10 pr-10 py-2.5 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                placeholder="Masukkan password">
                            <button type="button" onclick="togglePasswordMobile()" class="toggle-pass absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg id="eyeOpenM" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg id="eyeClosedM" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="btnLoginMobile"
                        class="btn-login ripple w-full py-3 rounded-xl text-white font-bold text-sm tracking-wide flex items-center justify-center gap-2 mt-2 anim-fade-up" style="animation-delay:0.4s">
                        <span id="btnTextM">Masuk</span>
                        <div id="btnSpinnerM" class="spinner"></div>
                        <svg id="btnArrowM" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <p class="mt-5 text-sm text-slate-500">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-bold text-emerald-600 hover:text-emerald-700">Daftar Akun</a>
                </p>
                <p class="mt-2 text-[10px] text-slate-400">&copy; {{ date('Y') }} SIJAMAAH</p>
                <a href="javascript:void(0)" id="btnInstallPwaM" class="hidden mt-2 inline-flex items-center justify-center gap-1 text-[11px] font-semibold text-emerald-600">Pasang Aplikasi</a>
            </div>
        </div>
    </div>

    <script>
        // === Clear History & Block Back ===
        (function() {
            try { localStorage.clear(); } catch(e) {}
            try { sessionStorage.clear(); } catch(e) {}
            history.replaceState(null, '', window.location.href);
            window.addEventListener('popstate', function() {
                history.pushState(null, '', window.location.href);
                window.location.href = '{{ route("login") }}';
            });
        })();
        // === Typing Animation ===
        (function() {
            var t = "SIJAMAAH", s = "Sistem Informasi Berjamaah";
            var targets = [
                { te: document.getElementById('tTitle'), se: document.getElementById('tSub'), cur: document.getElementById('cursorSub'), startDelay: 600 },
                { te: document.getElementById('tTitleM'), se: document.getElementById('tSubM'), cur: document.getElementById('cursorSubM'), startDelay: 800 }
            ];
            targets.forEach(function(target) {
                if (!target.te || !target.se) return;
                target.cur.style.display = 'none';
                var i = 0;
                function typeT() {
                    if (i < t.length) { target.te.textContent += t.charAt(i); i++; setTimeout(typeT, 120); }
                    else { i = 0; setTimeout(typeS, 400); }
                }
                function typeS() {
                    if (i < s.length) { target.se.textContent += s.charAt(i); i++; setTimeout(typeS, 45); }
                    else { target.cur.style.display = 'inline'; }
                }
                setTimeout(typeT, target.startDelay);
            });
        })();

        // === Particles ===
        (function() {
            const container = document.getElementById('particles');
            const count = window.innerWidth < 768 ? 12 : 25;
            for (let i = 0; i < count; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                const size = Math.random() * 8 + 3;
                p.style.width = size + 'px';
                p.style.height = size + 'px';
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDuration = (Math.random() * 10 + 8) + 's';
                p.style.animationDelay = (Math.random() * 8) + 's';
                container.appendChild(p);
            }
        })();

        // === Password Toggle Desktop ===
        function togglePassword() {
            const pw = document.getElementById('password');
            const open = document.getElementById('eyeOpen');
            const closed = document.getElementById('eyeClosed');
            if (pw.type === 'password') {
                pw.type = 'text';
                open.classList.add('hidden');
                closed.classList.remove('hidden');
            } else {
                pw.type = 'password';
                open.classList.remove('hidden');
                closed.classList.add('hidden');
            }
        }

        // === Password Toggle Mobile ===
        function togglePasswordMobile() {
            const pw = document.getElementById('passwordMobile');
            const open = document.getElementById('eyeOpenM');
            const closed = document.getElementById('eyeClosedM');
            if (pw.type === 'password') {
                pw.type = 'text';
                open.classList.add('hidden');
                closed.classList.remove('hidden');
            } else {
                pw.type = 'password';
                open.classList.remove('hidden');
                closed.classList.add('hidden');
            }
        }

        // === Cari Akun (bantuan login) ===
        function initCariAkun(prefix, formId) {
            const btn = document.getElementById(prefix + 'btnCariAkun');
            const panel = document.getElementById(prefix + 'panelCariAkun');
            const input = document.getElementById(prefix + 'cariNama');
            const hasil = document.getElementById(prefix + 'hasilCari');
            const kosong = document.getElementById(prefix + 'cariKosong');
            const loading = document.getElementById(prefix + 'cariLoading');
            const catatan = document.getElementById(prefix + 'cariCatatan');
            const chev = document.getElementById(prefix + 'chevCari');
            const form = document.getElementById(formId);
            if (!btn || !panel || !input || !form) return;
            const mailField = form.querySelector('input[name=email]');
            const pwField = form.querySelector('input[name=password]');
            let opened = false, timer = null;

            function setMsg(el, msg) {
                if (msg) { el.textContent = msg; el.classList.remove('hidden'); } else { el.classList.add('hidden'); }
            }
            function showPanel(open) {
                opened = open;
                panel.classList.toggle('hidden', !open);
                catatan.classList.toggle('hidden', !open);
                if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
                if (open) {
                    setTimeout(function () { input.focus(); }, 50);
                    if (input.value.trim().length >= 2) runCari();
                }
            }
            function runCari() {
                const q = input.value.trim();
                if (q.length < 2) { loading.classList.add('hidden'); setMsg(kosong, null); return; }
                loading.classList.remove('hidden');
                setMsg(kosong, null);
                fetch('/akun/cari?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        loading.classList.add('hidden');
                        hasil.innerHTML = '';
                        const accounts = (data && data.accounts) || [];
                        if (!accounts.length) { setMsg(kosong, 'Tidak ada akun yang cocok dengan "' + q + '".'); return; }
                        accounts.forEach(function (a) {
                            const ini = (a.name || '?').split(/\s+/).filter(Boolean).slice(0, 2)
                                .map(function (w) { return w.charAt(0).toUpperCase(); }).join('');
                            const nm = document.createElement('p');
                            nm.className = 'text-sm font-semibold text-slate-800 truncate';
                            nm.textContent = a.name;
                            const ml = document.createElement('p');
                            ml.className = 'text-[10px] text-slate-400 truncate';
                            ml.textContent = a.email;
                            const em = document.createElement('div');
                            em.className = 'flex-1 min-w-0';
                            em.appendChild(nm); em.appendChild(ml);
                            const av = document.createElement('span');
                            av.className = 'w-8 h-8 rounded-full bg-teal-100 text-teal-600 text-xs font-bold flex items-center justify-center flex-shrink-0';
                            av.textContent = ini || '?';
                            const bd = document.createElement('span');
                            bd.className = 'text-[10px] font-semibold rounded-full px-2 py-1 flex-shrink-0 ' +
                                (a.role === 'admin' ? 'bg-teal-100 text-teal-600' :
                                    a.role === 'musyrif' ? 'bg-amber-50 text-amber-600' :
                                        'bg-emerald-50 text-slate-600');
                            bd.textContent = a.roleLabel;
                            const row = document.createElement('div');
                            row.className = 'flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-100 cursor-pointer transition mb-1';
                            row.title = 'Klik untuk memakai email ini';
                            [av, em, bd].forEach(function (n) { row.appendChild(n); });
                            row.addEventListener('click', function () {
                                mailField.value = a.email;
                                mailField.style.borderColor = '#10b981';
                                mailField.style.boxShadow = '0 0 0 4px rgba(16,185,129,0.12)';
                                setTimeout(function () { mailField.style.borderColor = ''; mailField.style.boxShadow = ''; }, 2500);
                                showPanel(false);
                                hasil.innerHTML = '';
                                input.value = '';
                                if (pwField) pwField.focus();
                            });
                            hasil.appendChild(row);
                        });
                    })
                    .catch(function () { loading.classList.add('hidden'); setMsg(kosong, 'Terjadi kesalahan. Coba lagi.'); });
            }
            btn.addEventListener('click', function () { showPanel(!opened); });
            input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(runCari, 350); });
            input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); runCari(); } });
            document.addEventListener('click', function (e) {
                if (opened && !panel.contains(e.target) && !btn.contains(e.target)) showPanel(false);
            });
        }
        initCariAkun('', 'loginForm');
        initCariAkun('M', 'loginFormMobile');

        // === Loading Spinner ===
        ['loginForm', 'loginFormMobile'].forEach(function(id) {
            const form = document.getElementById(id);
            if (!form) return;
            const isMobile = id.includes('Mobile');
            form.addEventListener('submit', function() {
                const btn = document.getElementById(isMobile ? 'btnLoginMobile' : 'btnLogin');
                const txt = document.getElementById(isMobile ? 'btnTextM' : 'btnText');
                const spin = document.getElementById(isMobile ? 'btnSpinnerM' : 'btnSpinner');
                const arrow = document.getElementById(isMobile ? 'btnArrowM' : 'btnArrow');
                txt.textContent = 'Memproses...';
                spin.style.display = 'inline-block';
                arrow.style.display = 'none';
                btn.disabled = true;
                btn.style.opacity = '0.7';
            });
        });
    </script>

    <div id="pwaDlg" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:200;align-items:center;justify-content:center;padding:16px;background:rgba(15,23,42,0.55)" onclick="if(event.target===this)PwaDlgClose()">
        <div style="background:#ffffff;border-radius:16px;box-shadow:0 20px 50px rgba(0,0,0,0.3);width:100%;max-width:360px;padding:20px">
            <p style="font-size:14px;font-weight:bold;color:#1e293b;margin-bottom:4px">Pasang Aplikasi SIJAMAAH</p>
            <p style="font-size:11px;color:#64748b;margin-bottom:12px">Buka lewat Chrome/Edge/Safari terbaru dengan <b>https://sijamaah.rf.gd</b></p>
            <div id="pwaDlgIos" style="display:none">
                <ol style="font-size:12px;color:#475569;line-height:1.7;padding-left:18px">
                    <li>Buka ikon <b>Bagikan</b> (kotak panah) di Safari</li>
                    <li>Pilih <b>Tambahkan ke Layar Utama</b></li>
                    <li>Ketuk <b>Tambah</b></li>
                </ol>
            </div>
            <div id="pwaDlgWeb">
                <ol style="font-size:12px;color:#475569;line-height:1.7;padding-left:18px">
                    <li>Klik ikon <b>Install / Pasang</b> di address bar (atau menu <b>&#8942;</b> / <b>&#8230;</b>)</li>
                    <li>Pilih <b>Install</b> pada konfirmasi</li>
                </ol>
            </div>
            <div id="pwaDlgAndroid" style="display:none">
                <ol style="font-size:12px;color:#475569;line-height:1.7;padding-left:18px">
                    <li>Buka menu <b>&#8942;</b> di pojok kanan atas Chrome</li>
                    <li>Pilih <b>Install app</b> / <b>Tambahkan aplikasi ke layar utama</b></li>
                    <li>Ketuk <b>Install</b></li>
                </ol>
            </div>
            <button onclick="PwaDlgClose()" style="margin-top:16px;width:100%;padding:10px 0;border-radius:10px;background:#047857;color:#ffffff;font-size:14px;font-weight:bold;border:none;cursor:pointer">Tutup</button>
        </div>
    </div>

    <script>
        (function () {
            var KEY = 'sijamaah_pwa_installed';
            function PwaShowBtn(show) {
                var b1 = document.getElementById('btnInstallPwaD');
                var b2 = document.getElementById('btnInstallPwaM');
                if (b1) b1.classList.toggle('hidden', !show);
                if (b2) b2.classList.toggle('hidden', !show);
            }
            function isInstalledMode() {
                if (navigator.standalone) return true;
                if (window.matchMedia) {
                    var modes = ['standalone', 'fullscreen', 'minimal-ui'];
                    for (var i = 0; i < modes.length; i++) {
                        if (window.matchMedia('(display-mode: ' + modes[i] + ')').matches) return true;
                    }
                }
                if (/^(android-app|moz-extension|chrome-extension):\/\//i.test(document.referrer || '')) return true;
                try {
                    if (window.localStorage.getItem(KEY) === '1') return true;
                } catch (e) {}
                return false;
            }
            function markInstalled() {
                try { window.localStorage.setItem(KEY, '1'); } catch (e) {}
                PwaShowBtn(false);
            }
            var deferred = null;
            PwaShowBtn(false);
            if (isInstalledMode()) {
                markInstalled();
            }
            window.addEventListener('beforeinstallprompt', function (e) {
                e.preventDefault();
                deferred = e;
                if (!isInstalledMode()) PwaShowBtn(true);
            });
            window.addEventListener('appinstalled', function () { deferred = null; markInstalled(); });
            function tapInstall() {
                if (deferred) {
                    deferred.prompt();
                    if (deferred.userChoice) deferred.userChoice.then(function (choice) {
                        if (choice && choice.outcome === 'accepted') { deferred = null; markInstalled(); return; }
                        deferred = null; PwaShowBtn(false);
                    });
                    return;
                }
                PwaDlgOpen();
            }
            var b1 = document.getElementById('btnInstallPwaD');
            var b2 = document.getElementById('btnInstallPwaM');
            if (b1) b1.addEventListener('click', function (e) { e.preventDefault(); tapInstall(); });
            if (b2) b2.addEventListener('click', function (e) { e.preventDefault(); tapInstall(); });
            var ua = navigator.userAgent || '';
            var isIos = /iPhone|iPad|iPod/i.test(ua);
            var isAndroid = /Android/i.test(ua);
            var which = isIos ? 'pwaDlgIos' : (isAndroid ? 'pwaDlgAndroid' : 'pwaDlgWeb');
            ['pwaDlgIos', 'pwaDlgWeb', 'pwaDlgAndroid'].forEach(function (id) {
                document.getElementById(id).style.display = (id === which) ? 'block' : 'none';
            });
        })();
        function PwaDlgOpen() { var d = document.getElementById('pwaDlg'); if (d) { d.style.display = 'flex'; } }
        function PwaDlgClose() { var d = document.getElementById('pwaDlg'); if (d) { d.style.display = 'none'; } }
    </script>
</body>
</html>
