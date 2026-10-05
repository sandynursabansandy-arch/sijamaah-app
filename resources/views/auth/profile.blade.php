<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — SANTRI.ID</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .alert-animate { animation: slideUp 0.5s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-lg mx-auto px-4 mt-5">

        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Profil Saya</h1>
                <p class="text-[11px] text-slate-400">Kelola informasi akun Anda</p>
            </div>
        </div>

        <!-- Success -->
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center gap-2 text-sm alert-animate animate-fade-in">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Avatar + Info Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-4 animate-slide-up" style="animation-delay:0.05s">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 shrink-0 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-extrabold text-2xl ring-4 ring-emerald-100 select-none">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold text-slate-800 truncate">{{ $user->name }}</p>
                    <p class="text-[12px] text-slate-400">{{ $user->email }}</p>
                    @php
                        $jabatanSantri = $santri?->jabatan;
                        $roleLabel = $jabatanSantri ?: match ($user->role ?? '') {
                            'admin' => 'Administrator',
                            'musyrif' => 'Musyrif',
                            'pengasuh' => 'Pengasuh',
                            default => ucfirst($user->role ?? 'Pengguna'),
                        };
                    @endphp
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide
                        {{ $user->role === 'admin' ? 'bg-red-100 text-red-600' : ($user->role === 'musyrif' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600') }}">
                        {{ $roleLabel }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Detail Info -->
        <div class="bg-white rounded-xl shadow-lg p-5 mb-4 animate-slide-up" style="animation-delay:0.1s">
            <h2 class="text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-3">Informasi Akun</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-[12px] text-slate-500">Email</span>
                    <span class="text-[13px] font-medium text-slate-700">{{ $user->email }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-[12px] text-slate-500">Role</span>
                    <span class="text-[13px] font-medium text-slate-700">{{ $roleLabel }}</span>
                </div>
                @if($santri)
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-[12px] text-slate-500">Kamar</span>
                    <span class="text-[13px] font-medium text-slate-700">{{ $santri->kamar?->nama_kamar ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-[12px] text-slate-500">NIS</span>
                    <span class="text-[13px] font-medium text-slate-700">{{ $santri->nis ?? '-' }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between py-2">
                    <span class="text-[12px] text-slate-500">Terdaftar</span>
                    <span class="text-[13px] font-medium text-slate-700">{{ $user->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Ukuran Font -->
        <form method="POST" action="{{ route('profile.font-size') }}" class="bg-white rounded-xl shadow-lg p-6 mb-4 animate-slide-up" style="animation-delay:0.15s">
            @csrf

            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M12 5v14m-4-14l-6 14m18-14l-6 14M8 13h8"/></svg>
                <h2 class="text-[12px] font-bold uppercase tracking-wide text-slate-500">Ukuran Font</h2>
            </div>

            <p class="text-[12px] text-slate-500 mb-3">Pilih ukuran huruf yang nyaman untuk tampilan aplikasi (berlaku di semua halaman).</p>

            <div class="grid grid-cols-3 gap-2">
                @php $fs = old('font_size', $user->font_size ?? 'sedang'); @endphp
                @foreach(['kecil' => 'Kecil', 'sedang' => 'Sedang', 'besar' => 'Besar'] as $val => $label)
                    @php $sizeCls = ['kecil' => 'text-sm', 'sedang' => 'text-base', 'besar' => 'text-lg']; $sel = $fs === $val; @endphp
                    <label class="cursor-pointer">
                        <input type="radio" name="font_size" value="{{ $val }}" class="peer sr-only" onchange="this.form.submit()" {{ $sel ? 'checked' : '' }}>
                        <div class="rounded-xl border-2 py-3 text-center font-semibold transition-all
                            {{ $sizeCls[$val] }}
                            {{ $sel ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-sm' : 'border-slate-200 bg-slate-50 text-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700' }}">
                            Aa
                        </div>
                        <span class="block text-center text-[10px] font-bold mt-1 {{ $sel ? 'text-emerald-600' : 'text-slate-400' }}">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Pengaturan tersimpan otomatis saat dipilih.</p>
        </form>

        <!-- Edit Nama -->
        <form method="POST" action="{{ route('profile.update') }}" class="bg-white rounded-xl shadow-lg p-6 animate-slide-up" style="animation-delay:0.2s">
            @csrf
            @method('PUT')

            <h2 class="text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-3">Ubah Nama</h2>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full border-2 border-slate-200 focus:border-emerald-500 focus:ring-0 focus:shadow-[0_0_0_4px_rgba(16,185,129,0.1)] py-3 px-4 rounded-xl bg-slate-50 text-slate-800 text-sm transition-all duration-200"
                    placeholder="Nama lengkap Anda">
                @error('name')
                    <p class="text-red-500 text-[11px] mt-1 pl-0.5">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full mt-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-bold text-sm tracking-wide hover:from-emerald-600 hover:to-emerald-700 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 transition-all">
                Simpan Perubahan
            </button>
        </form>

        <!-- Ganti Password -->
        <form method="POST" action="{{ route('password.update') }}" class="bg-white rounded-xl shadow-lg p-6 mt-4 animate-slide-up" style="animation-delay:0.2s">
            @csrf

            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <h2 class="text-[12px] font-bold uppercase tracking-wide text-slate-500">Ganti Password</h2>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Password Lama</label>
                    <input type="password" name="current_password" required
                        class="w-full border-2 border-slate-200 focus:border-emerald-500 focus:ring-0 focus:shadow-[0_0_0_4px_rgba(16,185,129,0.1)] py-2.5 px-4 rounded-xl bg-slate-50 text-slate-800 text-sm transition-all duration-200"
                        placeholder="Masukkan password lama">
                    @error('current_password')
                        <p class="text-red-500 text-[11px] mt-1 pl-0.5">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Password Baru</label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full border-2 border-slate-200 focus:border-emerald-500 focus:ring-0 focus:shadow-[0_0_0_4px_rgba(16,185,129,0.1)] py-2.5 px-4 rounded-xl bg-slate-50 text-slate-800 text-sm transition-all duration-200"
                        placeholder="Min. 6 karakter">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-500 mb-1.5 pl-0.5">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required minlength="6"
                        class="w-full border-2 border-slate-200 focus:border-emerald-500 focus:ring-0 focus:shadow-[0_0_0_4px_rgba(16,185,129,0.1)] py-2.5 px-4 rounded-xl bg-slate-50 text-slate-800 text-sm transition-all duration-200"
                        placeholder="Ulangi password baru">
                    @error('password')
                        <p class="text-red-500 text-[11px] mt-1 pl-0.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit"
                class="w-full mt-4 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-bold text-sm tracking-wide hover:from-indigo-600 hover:to-indigo-700 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 transition-all">
                Simpan Password Baru
            </button>
        </form>
    </div>
</body>
</html>

