<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User — SIJAMAAH</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .animate-slide-in { animation: slideIn 0.5s ease-out both; }
        .alert-animate { animation: slideUp 0.5s ease-out; }
        tbody tr { animation: fadeIn 0.3s ease-out both; }
        .pw-mask { filter: blur(5px); }
        @for($i = 1; $i <= 20; $i++)
            tbody tr:nth-child({{ $i }}) { animation-delay: {{ $i * 0.04 }}s; }
        @endfor
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">

        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Kelola User</h1>
                <p class="text-[11px] text-slate-400">Tambah, edit, dan hapus akun pengguna</p>
            </div>
        </div>

        <!-- Success / Error -->
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center gap-2 text-sm alert-animate animate-fade-in">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2 text-sm alert-animate animate-fade-in">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Form Tambah User -->
        <div class="bg-white rounded-xl shadow-lg p-5 mb-5 animate-slide-up" style="animation-delay:0.05s">
            <h2 class="text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-3">Tambah User Baru</h2>
            <form method="POST" action="{{ route('user.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama lengkap"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="email@contoh.com"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="Min. 6 karakter"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Role</label>
                    <select name="role" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                        <option value="pengasuh">Pengasuh</option>
                        <option value="musyrif">Musyrif</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="bg-emerald-500 text-white px-5 py-2 rounded-lg font-semibold text-[13px] hover:bg-emerald-600 transition shadow-sm">
                        + Tambah User
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel User -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden animate-slide-up" style="animation-delay:0.1s">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">#</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Nama</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Email</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Password</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Role</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Terdaftar</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($users as $i => $user)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-4 py-2.5 text-[12px] text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2.5 font-semibold text-[13px]">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-[12px] text-slate-500">{{ $user->email }}</td>
                                <td class="px-4 py-2.5 text-[12px]">
                                    @if(!empty($user->password_plain))
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="pw-text font-mono text-[12px] text-slate-700 select-all pw-mask">{{ $user->password_plain }}</span>
                                            <button type="button" class="pw-toggle px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">Lihat</button>
                                        </span>
                                    @else
                                        <span class="text-slate-300" title="Password lama tidak tersimpan, gunakan Reset PW">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('user.updateRole', $user) }}" class="inline-flex items-center gap-1"
                                              onsubmit="return confirm('Ubah role {{ $user->name }} menjadi ' + document.getElementById('role-{{ $user->id }}').value + '?')">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" id="role-{{ $user->id }}" onchange="this.closest('form').submit()"
                                                class="border border-slate-200 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide focus:outline-none cursor-pointer
                                                    {{ $user->role === 'admin' ? 'bg-red-50 text-red-600' : ($user->role === 'musyrif' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="musyrif" {{ $user->role === 'musyrif' ? 'selected' : '' }}>Musyrif</option>
                                                <option value="pengasuh" {{ $user->role === 'pengasuh' ? 'selected' : '' }}>Pengasuh</option>
                                            </select>
                                        </form>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide
                                            {{ $user->role === 'admin' ? 'bg-red-100 text-red-600' : ($user->role === 'musyrif' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600') }}">
                                            {{ $user->role }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-[12px] text-slate-400">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-1">
                                        <!-- Reset Password -->
                                        <form method="POST" action="{{ route('user.resetPassword', $user) }}" class="inline reset-pw-form">
                                            @csrf
                                            <input type="hidden" name="new_password" class="reset-pw-input" value="" data-name="{{ $user->name }}">
                                            <button type="button" title="Reset password (sistem membuat password acak)"
                                                class="reset-pw-btn px-2 py-1 rounded text-[10px] font-semibold bg-amber-100 text-amber-600 hover:bg-amber-200 transition">
                                                Reset PW
                                            </button>
                                        </form>
                                        <!-- Hapus -->
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('user.destroy', $user) }}" class="inline"
                                                  onsubmit="return confirm('Hapus user {{ $user->name }}? Tindakan ini tidak bisa dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus user"
                                                    class="px-2 py-1 rounded text-[10px] font-semibold bg-red-100 text-red-600 hover:bg-red-200 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400 text-sm">
                                    Belum ada user terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Reset Password Modal -->
    <div id="resetPwModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[1010]">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 animate-slide-up">
            <div class="p-6 text-center">
                <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-1">Reset Password</h3>
                <p class="text-sm text-slate-500 mb-4">Password baru untuk <strong id="resetPwUserName" class="text-slate-700"></strong></p>

                <div class="bg-amber-50 border border-dashed border-amber-300 rounded-xl px-4 py-3 mb-4">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-amber-500 mb-1">Password baru (otomatis, berbeda tiap klik)</p>
                    <p id="resetPwValue" class="font-mono text-lg font-extrabold text-slate-800 tracking-wider select-all break-all"></p>
                </div>

                <div class="flex flex-wrap gap-2 mb-5">
                    <button type="button" id="resetPwCopy" class="flex-1 px-3 py-2 rounded-xl bg-slate-100 text-slate-600 text-[13px] font-medium hover:bg-slate-200 transition">Salin</button>
                    <button type="button" id="resetPwRegen" class="flex-1 px-3 py-2 rounded-xl bg-slate-100 text-slate-600 text-[13px] font-medium hover:bg-slate-200 transition">Acak Lagi</button>
                </div>

                <div class="flex gap-3">
                    <button id="resetPwCancel" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all duration-200">Batal</button>
                    <button id="resetPwConfirm" class="flex-1 px-4 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition-all duration-200 shadow-md shadow-amber-200">Ya, Reset</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('.pw-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var text = btn.parentElement.querySelector('.pw-text');
                    var masked = text.classList.toggle('pw-mask');
                    btn.textContent = masked ? 'Lihat' : 'Sembunyi';
                });
            });

            var modal = document.getElementById('resetPwModal');
            var pwValueEl = document.getElementById('resetPwValue');
            var pwNameEl = document.getElementById('resetPwUserName');
            var copyBtn = document.getElementById('resetPwCopy');
            var regenBtn = document.getElementById('resetPwRegen');
            var cancelBtn = document.getElementById('resetPwCancel');
            var confirmBtn = document.getElementById('resetPwConfirm');
            var activeForm = null;

            var CHARS = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';

            function generatePassword() {
                var out = '', buf = new Uint32Array(12);
                if (window.crypto && crypto.getRandomValues) {
                    crypto.getRandomValues(buf);
                } else {
                    for (var i = 0; i < buf.length; i++) buf[i] = Math.floor(Math.random() * 4294967296);
                }
                for (var i = 0; i < 10; i++) out += CHARS[buf[i] % CHARS.length];
                return out;
            }

            function showModal() {
                pwValueEl.textContent = generatePassword();
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
                activeForm = null;
            }

            document.querySelectorAll('.reset-pw-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    activeForm = btn.closest('.reset-pw-form');
                    var name = activeForm.querySelector('.reset-pw-input').dataset.name || '';
                    pwNameEl.textContent = name;
                    showModal();
                });
            });

            regenBtn.addEventListener('click', function () {
                pwValueEl.textContent = generatePassword();
            });

            copyBtn.addEventListener('click', function () {
                var txt = pwValueEl.textContent;
                var done = function () {
                    var old = copyBtn.textContent;
                    copyBtn.textContent = 'Tersalin!';
                    copyBtn.classList.add('bg-emerald-100', 'text-emerald-600');
                    setTimeout(function () {
                        copyBtn.textContent = old;
                        copyBtn.classList.remove('bg-emerald-100', 'text-emerald-600');
                    }, 1200);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(txt).then(done, done);
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = txt; document.body.appendChild(ta); ta.select();
                    try { document.execCommand('copy'); } catch (e) {}
                    document.body.removeChild(ta);
                    done();
                }
            });

            cancelBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

            confirmBtn.addEventListener('click', function () {
                if (!activeForm) return;
                activeForm.querySelector('.reset-pw-input').value = pwValueEl.textContent;
                activeForm.submit();
            });
        })();
    </script>
</body>
</html>
