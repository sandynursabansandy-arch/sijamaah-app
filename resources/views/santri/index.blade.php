<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Santri</title>
    @vite('resources/css/app.css')
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
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out both;
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-in both;
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

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #7f1d1d;
        }

        .alert-animate {
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .filter-gradient { background: #ffffff; border: 1px solid #e2e8f0; }
        .animate-fade-in-fast { animation: fadeInFast 0.15s ease-out both; }
        @keyframes fadeInFast { from { opacity: 0; transform: translateY(-6px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-5 animate-slide-in relative z-30">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-white shadow-lg flex items-center justify-center overflow-hidden border-[3px] border-white ring-2 ring-emerald-200">
                        <img src="{{ asset('images/image.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                            Kelola Santri
                        </h1>
                        <p class="text-slate-500 text-xs">Tambah, edit, atau hapus data santri pesantren</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 items-center">
                    <a href="{{ route('presensi.index') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Dashboard</a>
                    <a href="{{ route('presensi.rekap') }}" class="bg-purple-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Rekap Presensi</a>
                    <a href="{{ route('presensi.rankingBerjamaah') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Ranking Berjamaah</a>
                    <a href="{{ route('presensi.rekapBerjamaah') }}" class="bg-teal-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Rekap Berjamaah</a>
                    <a href="{{ route('presensi.rankingAlfa') }}" class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover-lift shadow text-xs">Ranking Alfa</a>
                    <!-- Settings Dropdown -->
                    <div class="relative" id="settingsSantri">
                        <button onclick="toggleDropdown('settingsSantri')" class="bg-slate-600 text-white px-3 py-2 rounded-lg font-semibold hover:bg-slate-700 shadow text-xs transition-smooth flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="hidden sm:inline">Akun</span>
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

        <!-- Success Message (toast) -->
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

        <!-- Filter & Search Section -->
        <form method="GET" action="{{ route('santri.index') }}" class="filter-gradient rounded-xl p-3 mb-5 animate-fade-in shadow-sm">
            <div class="flex flex-wrap items-end gap-2.5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Filter Rayon</label>
                    <select name="kamar_id" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                        <option value="">Semua Rayon</option>
                        @foreach($daftarKamar as $kamar)
                            <option value="{{ $kamar->id }}" {{ $kamarId == $kamar->id ? 'selected' : '' }}>
                                {{ $kamar->nama_kamar }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Cari Santri</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama atau NIS..." 
                        class="w-full sm:w-48 border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                </div>
                <div class="ml-auto">
                    <button type="submit" class="bg-emerald-500 text-white py-1.5 px-4 rounded-md font-semibold hover:bg-emerald-600 shadow transition-smooth text-[13px] h-[34px] box-border">
                        Cari
                    </button>
                </div>
            </div>
        </form>

        <!-- Add Button -->
        <div class="mb-5 flex items-center justify-between animate-slide-up">
            @if(auth()->user()?->canManagePresensi())
                <a href="{{ route('santri.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover-lift shadow-lg transform transition-smooth">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Santri
                </a>
                <a href="{{ route('santri.import.form') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover-lift shadow-lg transform transition-smooth">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import CSV
                </a>
            @endif
        </div>

        <!-- Santri Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in border border-slate-100">
            <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead>
                            <tr class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
                                <th class="px-2 py-2.5 text-center text-[13px] font-bold w-[4%]"><span class="th-badge">No</span></th>
                                <th class="px-2 py-2.5 text-center text-[13px] font-bold w-[26%]"><span class="th-badge">Nama</span></th>
                                <th class="px-2 py-2.5 text-left w-[18%]"><span class="th-badge">Kamar</span></th>
                                <th class="px-2 py-2.5 text-left w-[16%]"><span class="th-badge">Kelas</span></th>
                                <th class="px-2 py-2.5 text-center w-[16%]"><span class="th-badge">Jabatan</span></th>
                                @if(auth()->user()?->canManagePresensi())
                                    <th class="px-2 py-2.5 text-center w-[20%]"><span class="th-badge">Aksi</span></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($santris as $index => $santri)
                                <tr class="hover:bg-slate-50 transition-smooth">
                                    <td class="px-2 py-2.5 text-center font-semibold text-slate-500 text-[13px]">{{ ($santris->currentPage() - 1) * 15 + $index + 1 }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-blue-400 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                            {{ substr($santri->nama, 0, 1) }}
                                        </div>
                                        <p class="font-semibold text-slate-900 text-[13px]">{{ $santri->nama }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-slate-600 text-[13px]">{{ $santri->kamar->nama_kamar }}</td>
                                <td class="px-3 py-2.5 text-slate-600 text-[13px]">{{ $santri->kelas }}</td>
                                <td class="px-3 py-2.5 text-left text-[13px]">
                                    @if($santri->jabatan)
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-medium text-[12px]">{{ $santri->jabatan }}</span>
                                    @else
                                        <span class="text-slate-400 text-[12px]">-</span>
                                    @endif
                                </td>
                                @if(auth()->user()?->canManagePresensi())
                                    <td class="px-3 py-2.5 text-center">
                                        <div class="flex gap-1.5 justify-center">
                                            <a href="{{ route('santri.edit', $santri->id) }}"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md text-[11px] font-semibold transition-smooth">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('santri.destroy', $santri->id) }}" class="inline"
                                                onsubmit="return confirm('Yakin ingin menghapus santri ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md text-[11px] font-semibold transition-smooth">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()?->canManagePresensi() ? 6 : 5 }}" class="px-6 py-12 text-center">
                                    <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak ada santri</h3>
                                    <p class="text-slate-500 text-sm mb-3">Belum ada data santri yang cocok dengan pencarian Anda.</p>
                                    @if(auth()->user()?->canManagePresensi())
                                        <a href="{{ route('santri.create') }}" class="inline-block bg-emerald-500 text-white px-5 py-2 rounded-lg font-semibold hover-lift text-sm">
                                            Tambah Santri Pertama
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($santris->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
                    {{ $santris->links() }}
                </div>
            @endif
        </div>

        <!-- Info Cards -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3 animate-fade-in max-w-lg mx-auto">
            <div class="bg-white rounded-lg p-3 shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-[10px] font-semibold uppercase">Total Santri</p>
                <p class="text-xl font-bold text-emerald-600">{{ $santris->total() }}</p>
            </div>

            <div class="bg-white rounded-lg p-3 shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-[10px] font-semibold uppercase">Total Kamar</p>
                <p class="text-xl font-bold text-blue-600">{{ count($daftarKamar) }}</p>
            </div>

            <div class="bg-white rounded-lg p-3 shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-[10px] font-semibold uppercase">Halaman</p>
                <p class="text-xl font-bold text-purple-600">{{ $santris->currentPage() }} / {{ $santris->lastPage() }}</p>
            </div>
        </div>
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
