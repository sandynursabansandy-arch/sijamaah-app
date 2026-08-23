<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Santri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Cegah layout shift saat scrollbar muncul/hilang selama animasi masuk */
        html {
            overflow-y: scroll;
            scrollbar-gutter: stable;
        }

        body {
            overflow-x: hidden;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        .animate-slide-up {
            animation: slideUp 0.6s ease-out both;
        }

        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .filter-gradient { background: #ffffff; border: 1px solid #e2e8f0; }
        .animate-fade-in-fast { animation: fadeInFast 0.15s ease-out both; }
        @keyframes fadeInFast { from { opacity: 0; transform: translateY(-6px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        input:focus, select:focus, textarea:focus {
            outline: 2px solid #10b981;
            outline-offset: 1px;
        }

        .input-group {
            animation: slideUp 0.5s ease-out both;
        }

        .input-group:nth-child(1) { animation-delay: 0s; }
        .input-group:nth-child(2) { animation-delay: 0.05s; }
        .input-group:nth-child(3) { animation-delay: 0.1s; }
        .input-group:nth-child(4) { animation-delay: 0.15s; }
        .input-group:nth-child(5) { animation-delay: 0.2s; }
        .input-group:nth-child(6) { animation-delay: 0.25s; }

        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        #toast-container { position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 9999; pointer-events: none; }
        .toast {
            pointer-events: auto;
            display: flex; align-items: center; gap: 10px;
            padding: 14px 24px; border-radius: 12px;
            font-size: 14px; font-weight: 600; color: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.22);
            animation: toastIn 0.35s ease-out forwards;
            max-width: 420px;
        }
        .toast.success { background: #059669; }
        .toast.error   { background: #dc2626; }
        .toast.fade-out { animation: toastOut 0.3s ease-in forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0) scale(1); } to { opacity:0; transform:translateY(-20px) scale(0.95); } }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    <div id="toast-container"></div>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-5 animate-slide-in relative z-30">
                <div class="flex items-center justify-between">
                <div>
                    <a href="{{ route('santri.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 mb-3 text-xs font-medium transition-smooth">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Daftar Santri
                    </a>
                    <h1 class="text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                        Edit Santri
                    </h1>
                    <p class="text-slate-500 text-xs">Perbarui informasi santri</p>
                </div>
                <div class="flex gap-2 items-center">
                    <!-- Settings Dropdown -->
                    <div class="relative" id="settingsEdit">
                        <button onclick="toggleDropdown('settingsEdit')" class="bg-slate-600 text-white px-3 py-2 rounded-lg font-semibold hover:bg-slate-700 shadow text-xs transition-smooth flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-50 animate-fade-in-fast">
                            <a href="{{ route('password.change') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Ganti Password
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="button" onclick="showLogoutModal()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var container = document.getElementById('toast-container');
                    var toast = document.createElement('div');
                    toast.className = 'toast success';
                    toast.innerHTML = '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ session('success') }}';
                    container.appendChild(toast);
                    setTimeout(function() { toast.classList.add('fade-out'); }, 2800);
                    setTimeout(function() { toast.remove(); }, 3100);
                });
            </script>
        @endif

        <!-- Form Card -->
        <form method="POST" action="{{ route('santri.update', $santri->id) }}" class="bg-white rounded-xl shadow-lg p-6 animate-slide-up">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama', $santri->nama) }}" 
                        placeholder="Contoh: Ahmad Fadli" maxlength="100" required
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('nama')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Kamar/Asrama -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kamar/Asrama</label>
                    <input type="text" name="kamar" value="{{ old('kamar', $santri->kamar->nama_kamar) }}" 
                        placeholder="Contoh: Kamar A, Kamar B" maxlength="100" required
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('kamar')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Kelas -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kelas</label>
                    <input type="text" name="kelas" value="{{ old('kelas', $santri->kelas) }}" 
                        placeholder="Contoh: Kelas 1 Aliyah" maxlength="50" required
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('kelas')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Jabatan -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan', $santri->jabatan ?? '') }}" 
                        placeholder="Contoh: Ketua Kamar, Imam, Muadzin" maxlength="100"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('jabatan')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-5 flex gap-3">
                <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover-lift shadow-lg transform transition-smooth flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Perbarui Santri
                </button>
                <a href="{{ route('santri.index') }}" class="flex-1 bg-slate-200 text-slate-700 px-5 py-2.5 rounded-lg font-semibold text-sm hover-lift shadow-sm transform transition-smooth flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Batal
                </a>
            </div>

            <!-- Info Box -->
            <div class="mt-5 p-3 bg-blue-50 border-l-4 border-blue-400 rounded-lg">
                <p class="text-[12px] text-blue-700">
                    <strong>Tips:</strong> Lengkapi semua data santri dengan benar. Data akan digunakan untuk presensi sholat berjamaah.
                </p>
            </div>
        </form>
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
    </script>

    <script>
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
