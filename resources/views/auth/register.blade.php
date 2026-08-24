<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — SIJAMAAH</title>
    @vite('resources/css/app.css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', system-ui, sans-serif; }

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

        .glass-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 25px 60px rgba(0,0,0,0.15), 0 0 40px rgba(16,185,129,0.08);
        }

        .logo-pulse {
            animation: logoPulse 3s ease-in-out infinite;
        }
        @keyframes logoPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            50%      { transform: scale(1.03); box-shadow: 0 0 30px 10px rgba(16,185,129,0.15); }
        }

        .input-glow {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
        }
        .input-glow:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.1), 0 4px 12px rgba(16,185,129,0.08);
        }

        .btn-register {
            background: linear-gradient(135deg, #059669, #10b981, #34d399);
            background-size: 200% 200%;
            animation: btnGradient 3s ease infinite;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16,185,129,0.4);
        }
        @keyframes btnGradient {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

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

        .ornament-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .toggle-pass { transition: all 0.2s; }
        .toggle-pass:hover { color: #10b981; }

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
        .typing-cursor::after {
            content: '|';
            color: #5eead4;
            animation: blink 0.7s step-end infinite;
            font-weight: 300;
            opacity: 0.9;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0; }
        }
    </style>
</head>
<body class="min-h-screen bg-animated ornament-pattern overflow-x-hidden">

    <div id="particles" class="fixed inset-0 pointer-events-none z-0"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">

        <!-- ======= DESKTOP ======= -->
        <div class="hidden lg:flex w-full max-w-5xl rounded-[2rem] shadow-2xl overflow-hidden anim-slide-left" style="min-height: 540px;">

            <!-- Left Panel: Branding -->
            <div class="w-[45%] relative flex flex-col items-center justify-center p-8 sm:p-10 pt-2 pb-12 text-center rounded-[2rem]" style="background: linear-gradient(135deg, #064e3b, #065f46, #047857);">

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

            <!-- Right Panel: Register Form -->
            <div class="w-[55%] bg-white/95 backdrop-blur-xl flex items-center justify-center p-10 anim-slide-right">
                <div class="w-full max-w-[380px]">
                    <div class="mb-6">
                        <h2 class="text-2xl font-extrabold text-slate-800 mb-1">Buat Akun Baru</h2>
                        <p class="text-slate-500 text-sm">Daftar untuk mengakses sistem presensi</p>
                    </div>

                    @if($errors->any())
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-start gap-2 anim-fade-up">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                @foreach($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" id="registerForm" class="space-y-4" autocomplete="off">
                        @csrf

                        <!-- Nama -->
                        <div class="anim-fade-up" style="animation-delay:0.1s">
                            <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Nama Lengkap</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="off"
                                    class="input-glow w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                    placeholder="Nama lengkap">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="anim-fade-up" style="animation-delay:0.15s">
                            <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Email</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                </span>
                                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="off"
                                    class="input-glow w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                    placeholder="email@contoh.com">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="anim-fade-up" style="animation-delay:0.2s">
                            <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Password</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input type="password" name="password" id="passwordReg" required minlength="6" autocomplete="new-password"
                                    class="input-glow w-full pl-10 pr-10 py-3 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                    placeholder="Min. 6 karakter">
                                <button type="button" onclick="toggleRegPass()" class="toggle-pass absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg id="eyeOpenR" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg id="eyeClosedR" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="anim-fade-up" style="animation-delay:0.25s">
                            <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Konfirmasi Password</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                <input type="password" name="password_confirmation" required minlength="6" autocomplete="new-password"
                                    class="input-glow w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                    placeholder="Ulangi password">
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="anim-fade-up pt-1" style="animation-delay:0.3s">
                            <button type="submit" id="btnRegister"
                                class="btn-register ripple w-full py-3 rounded-xl text-white font-bold text-sm tracking-wide flex items-center justify-center gap-2">
                                <span id="btnTextR">Daftar Sekarang</span>
                                <div id="btnSpinnerR" class="spinner" style="border:3px solid rgba(255,255,255,0.3);border-top:3px solid white;border-radius:50%;width:20px;height:20px;animation:spin 0.7s linear infinite;display:none;"></div>
                                <svg id="btnArrowR" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>

                    <!-- Footer -->
                    <div class="mt-6 text-center anim-fade-up" style="animation-delay:0.4s">
                        <p class="text-sm text-slate-500">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline">Masuk</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======= MOBILE ======= -->
        <div class="lg:hidden w-full max-w-sm anim-slide-right">
            <div class="glass-card rounded-3xl p-8 text-center">
                <!-- Logo -->
                <div class="logo-pulse w-40 h-40 sm:w-60 sm:h-60 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-800 flex items-center justify-center mx-auto mb-6 shadow-xl p-4">
                    <img src="{{ asset('images/nw.png') }}" alt="Logo" class="w-full h-full object-contain drop-shadow-lg">
                </div>

                <h2 class="text-xl font-extrabold text-slate-800 mb-1">Buat Akun Baru</h2>
                <p class="text-slate-500 text-xs mb-5">Daftar untuk mengakses sistem presensi</p>

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs flex items-start gap-2 anim-fade-up">
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            @foreach($errors->all() as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="registerFormMobile" class="space-y-3 text-left" autocomplete="off">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1 pl-0.5">Nama Lengkap</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <input type="text" name="name" value="{{ old('name') }}" required autocomplete="off"
                                class="input-glow w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                placeholder="Nama lengkap">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1 pl-0.5">Email</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="off"
                                class="input-glow w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                placeholder="email@contoh.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1 pl-0.5">Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <input type="password" name="password" id="passwordRegM" required minlength="6" autocomplete="new-password"
                                class="input-glow w-full pl-10 pr-10 py-2.5 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                placeholder="Min. 6 karakter">
                            <button type="button" onclick="toggleRegPassM()" class="toggle-pass absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg id="eyeOpenRM" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg id="eyeClosedRM" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1 pl-0.5">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <input type="password" name="password_confirmation" required minlength="6" autocomplete="new-password"
                                class="input-glow w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 text-slate-800 text-sm focus:outline-none"
                                placeholder="Ulangi password">
                        </div>
                    </div>

                    <button type="submit" id="btnRegisterM"
                        class="btn-register ripple w-full py-3 rounded-xl text-white font-bold text-sm tracking-wide flex items-center justify-center gap-2 mt-1">
                        <span id="btnTextRM">Daftar Sekarang</span>
                        <div id="btnSpinnerRM" style="border:3px solid rgba(255,255,255,0.3);border-top:3px solid white;border-radius:50%;width:20px;height:20px;animation:spin 0.7s linear infinite;display:none;"></div>
                        <svg id="btnArrowRM" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <p class="mt-5 text-sm text-slate-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:text-emerald-700">Masuk</a>
                </p>
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
            var te = document.getElementById('tTitle');
            var se = document.getElementById('tSub');
            var cur = document.getElementById('cursorSub');
            if (!te || !se) return;
            cur.style.display = 'none';
            var i = 0;
            function typeT() {
                if (i < t.length) { te.textContent += t.charAt(i); i++; setTimeout(typeT, 120); }
                else { i = 0; setTimeout(typeS, 400); }
            }
            function typeS() {
                if (i < s.length) { se.textContent += s.charAt(i); i++; setTimeout(typeS, 45); }
                else { cur.style.display = 'inline'; }
            }
            setTimeout(typeT, 600);
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

        function toggleRegPass() {
            const pw = document.getElementById('passwordReg');
            const open = document.getElementById('eyeOpenR');
            const closed = document.getElementById('eyeClosedR');
            if (pw.type === 'password') { pw.type='text'; open.classList.add('hidden'); closed.classList.remove('hidden'); }
            else { pw.type='password'; open.classList.remove('hidden'); closed.classList.add('hidden'); }
        }
        function toggleRegPassM() {
            const pw = document.getElementById('passwordRegM');
            const open = document.getElementById('eyeOpenRM');
            const closed = document.getElementById('eyeClosedRM');
            if (pw.type === 'password') { pw.type='text'; open.classList.add('hidden'); closed.classList.remove('hidden'); }
            else { pw.type='password'; open.classList.remove('hidden'); closed.classList.add('hidden'); }
        }

        ['registerForm', 'registerFormMobile'].forEach(function(id) {
            const form = document.getElementById(id);
            if (!form) return;
            const isMobile = id.includes('Mobile');
            form.addEventListener('submit', function() {
                const btn = document.getElementById(isMobile ? 'btnRegisterM' : 'btnRegister');
                const txt = document.getElementById(isMobile ? 'btnTextRM' : 'btnTextR');
                const spin = document.getElementById(isMobile ? 'btnSpinnerRM' : 'btnSpinnerR');
                const arrow = document.getElementById(isMobile ? 'btnArrowRM' : 'btnArrowR');
                txt.textContent = 'Memproses...';
                spin.style.display = 'inline-block';
                arrow.style.display = 'none';
                btn.disabled = true;
                btn.style.opacity = '0.7';
            });
        });
    </script>
    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
</body>
</html>
