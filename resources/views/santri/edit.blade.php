<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Santri — SANTRI.ID</title>
    @vite(['resources/css/app.css'])
    <style>
        /* Cegah layout shift saat scrollbar muncul/hilang selama animasi masuk */
        html {
            scrollbar-gutter: stable;
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
            min-width: 0;
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

        /* ===== POPUP KONFIRMASI SIMPAN (CSS mandiri, tidak bergantung build Tailwind) ===== */
        #savePopup {
            position: fixed; inset: 0; z-index: 10050;
            display: none; align-items: center; justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, .55);
        }
        #savePopup.tampil { display: flex; }
        #savePopup .kartu-popup {
            width: 100%; max-width: 380px; padding: 28px 24px; text-align: center;
            background: #fff; border-radius: 18px;
            box-shadow: 0 30px 60px -18px rgba(2, 6, 23, .6);
            animation: popupMasuk .3s cubic-bezier(.22, 1, .36, 1) both;
        }
        #savePopup .kartu-popup h3 { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin-bottom: .4rem; }
        #savePopup .kartu-popup p { font-size: .875rem; color: #475569; line-height: 1.6; }
        #savePopup .kartu-popup .lencana {
            width: 60px; height: 60px; margin: 0 auto 1rem; border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            background: #d1fae5; color: #059669;
        }
        #savePopup .kartu-popup .lencana svg { width: 30px; height: 30px; }
        #savePopup .kartu-popup .tombol-ok {
            margin-top: 1.25rem; padding: .6rem 1.6rem; border: 0; border-radius: 10px;
            background: #059669; color: #fff; font-size: .875rem; font-weight: 600; cursor: pointer;
        }
        #savePopup .kartu-popup .tombol-ok:hover { background: #047857; }
        @keyframes popupMasuk {
            from { opacity: 0; transform: translateY(16px) scale(.96); }
            to   { opacity: 1; transform: none; }
        }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    <div id="toast-container"></div>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-5 animate-slide-in relative z-30">
                <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <a href="{{ route('santri.index', $filterKembali) }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 mb-3 text-xs font-medium transition-smooth">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Daftar Santri
                    </a>
                    <h1 class="text-xl sm:text-2xl md:text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
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

        <!-- Save Confirmation Popup -->
        <div id="savePopup">
            <div class="kartu-popup">
                <div class="lencana">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 id="savePopupTitle">Berhasil!</h3>
                <p id="savePopupMessage">Santri berhasil diperbarui.</p>
                <button type="button" onclick="closeSavePopup()" class="tombol-ok">Oke, Mengerti</button>
            </div>
        </div>
        <script>
            var savePopupTimer = null;
            var savePopupTick = null;

            function closeSavePopup() {
                var popup = document.getElementById('savePopup');
                if (!popup) return;
                popup.classList.remove('tampil');
                document.body.style.overflow = '';
                if (savePopupTimer) { clearTimeout(savePopupTimer); savePopupTimer = null; }
                if (savePopupTick) { clearInterval(savePopupTick); savePopupTick = null; }
            }
            document.getElementById('savePopup').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeSavePopup();
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    var popup = document.getElementById('savePopup');
                    if (popup && popup.classList.contains('tampil')) {
                        closeSavePopup();
                    }
                }
            });

            // Konfirmasi otomatis setelah halaman selesai dimuat (flash message).
            @if (session('success'))
            (function () {
                var popup = document.getElementById('savePopup');
                if (!popup) return;
                var judul = document.getElementById('savePopupTitle');
                if (judul) judul.textContent = @json(session('success_judul', 'Berhasil!'));
                var pesan = document.getElementById('savePopupMessage');
                if (pesan) pesan.textContent = @json(session('success'));

                var DETIK = 1;

                popup.classList.add('tampil');
                document.body.style.overflow = 'hidden';
                var tombol = popup.querySelector('button');
                if (tombol) tombol.focus();

                savePopupTimer = setTimeout(closeSavePopup, DETIK * 1000);
            })();
            @endif
        </script>

        <!-- Form Card -->
        <form method="POST" action="{{ route('santri.update', $santri->id) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-6 animate-slide-up">
            @csrf
            @method('PUT')

            {{-- Bawa parameter filter daftar santri, supaya setelah
                 disimpan dan kembali ke daftar, filter tidak reset. --}}
            @foreach($filterKembali as $kunci => $nilai)
                <input type="hidden" name="{{ $kunci }}" value="{{ $nilai }}">
            @endforeach

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

                @if($kolomJenisKelamin)
                    <!-- Jenis Kelamin -->
                    <div class="input-group">
                        <label class="block text-[13px] font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px] bg-white">
                            <option value="">- Pilih -</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin', $santri->jenis_kelamin ?? '') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin', $santri->jenis_kelamin ?? '') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

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

                {{-- Kelas Pengajian + Jenjang Pendidikan (dua jalur terpisah). --}}
                @include('santri._fields-kelas-ganda')

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

                <!-- Is Pengajar -->
                <div class="input-group">
                    <label class="flex flex-wrap items-center gap-2 text-[13px] font-semibold text-slate-700 mb-1 cursor-pointer">
                        <input type="checkbox" name="is_pengajar" value="1" {{ old('is_pengajar', $santri->is_pengajar ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                        <span>Ikut presensi (pengajar/mudir/mustahik/munawwib)</span>
                    </label>
                    <p class="text-[10px] text-slate-500 mt-1">Centang agar nama muncul di daftar absen presensi berjamaah</p>
                </div>

                <!-- No HP Wali -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">No. HP Wali</label>
                    <input type="text" name="no_hp_wali" value="{{ old('no_hp_wali', $santri->no_hp_wali ?? '') }}" 
                        placeholder="Contoh: 08123456789" maxlength="20"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('no_hp_wali')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- No WhatsApp -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">No. WhatsApp</label>
                    <input type="text" name="no_whatsapp" value="{{ old('no_whatsapp', $santri->no_whatsapp ?? '') }}" 
                        placeholder="Contoh: 08123456789" maxlength="20"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    <p class="text-[10px] text-slate-400 mt-1">Nomor WhatsApp yang bisa dihubungi langsung</p>
                    @error('no_whatsapp')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Separator -->
            <div class="mt-6 mb-4 border-t border-slate-200"></div>

            <!-- Address Section -->
            <h3 class="text-[14px] font-bold text-slate-700 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.25 8.25L12 13.5L6.75 8.25M12 13.5V3"/></svg>
                Alamat Lengkap Santri
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Provinsi -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Provinsi</label>
                    <input type="text" name="alamat_provinsi" value="{{ old('alamat_provinsi', $santri->alamat_provinsi ?? '') }}" 
                        placeholder="Contoh: Jawa Timur" maxlength="50"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_provinsi')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Kabupaten/Kota -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kabupaten/Kota</label>
                    <input type="text" name="alamat_kabupaten" value="{{ old('alamat_kabupaten', $santri->alamat_kabupaten ?? '') }}" 
                        placeholder="Contoh: Kota Pasuruan" maxlength="50"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_kabupaten')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Kecamatan -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kecamatan</label>
                    <input type="text" name="alamat_kecamatan" value="{{ old('alamat_kecamatan', $santri->alamat_kecamatan ?? '') }}" 
                        placeholder="Contoh: Purmera" maxlength="50"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_kecamatan')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Desa/Kelurahan -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Desa/Kelurahan</label>
                    <input type="text" name="alamat_desa" value="{{ old('alamat_desa', $santri->alamat_desa ?? '') }}" 
                        placeholder="Contoh: Desa Sidoarjo" maxlength="50"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_desa')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Dusun/Kampung -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Dusun/Kampung</label>
                    <input type="text" name="alamat_dusun" value="{{ old('alamat_dusun', $santri->alamat_dusun ?? '') }}" 
                        placeholder="Contoh: Dusun 1" maxlength="50"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_dusun')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- RT -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">RT</label>
                    <input type="text" name="alamat_rt" value="{{ old('alamat_rt', $santri->alamat_rt ?? '') }}" 
                        placeholder="Contoh: 03" maxlength="5"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_rt')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- RW -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">RW</label>
                    <input type="text" name="alamat_rw" value="{{ old('alamat_rw', $santri->alamat_rw ?? '') }}" 
                        placeholder="Contoh: 01" maxlength="5"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_rw')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Jalan -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Jalan</label>
                    <input type="text" name="alamat_jalan" value="{{ old('alamat_jalan', $santri->alamat_jalan ?? '') }}" 
                        placeholder="Contoh: Jln. Raya Sidoarjo" maxlength="100"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_jalan')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- No. Rumah -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">No. Rumah</label>
                    <input type="text" name="alamat_nomor_rumah" value="{{ old('alamat_nomor_rumah', $santri->alamat_nomor_rumah ?? '') }}" 
                        placeholder="Contoh: 45/B" maxlength="20"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('alamat_nomor_rumah')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Kode Pos -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kode Pos</label>
                    <input type="text" name="kode_pos" value="{{ old('kode_pos', $santri->kode_pos ?? '') }}" 
                        placeholder="Contoh: 61252" maxlength="10"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    @error('kode_pos')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Latitude -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Latitude (GPS)</label>
                    <input type="text" name="latitude" value="{{ old('latitude', $santri->latitude ?? '') }}" 
                        placeholder="Contoh: -7.5123456" maxlength="20"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    <p class="text-[10px] text-slate-400 mt-1">Opsional</p>
                    @error('latitude')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Longitude -->
                <div class="input-group">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Longitude (GPS)</label>
                    <input type="text" name="longitude" value="{{ old('longitude', $santri->longitude ?? '') }}" 
                        placeholder="Contoh: 112.3456789" maxlength="20"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
                    <p class="text-[10px] text-slate-400 mt-1">Opsional</p>
                    @error('longitude')
                        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Foto Rumah -->
            <div class="mt-4 input-group">
                <label class="block text-[13px] font-semibold text-slate-700 mb-1">Foto Rumah</label>
                <input type="file" name="foto_rumah" accept="image/*"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <p class="text-[10px] text-slate-400 mt-1">Maksimal 2MB. JPG, PNG, WEBP</p>
                @if($santri->foto_rumah)
                    <a href="{{ asset('storage/' . $santri->foto_rumah) }}" target="_blank" class="text-[10px] text-emerald-600 hover:underline block mt-1">Lihat foto yang ada</a>
                @endif
                @error('foto_rumah')
                    <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Consent Checkbox -->
            <div class="mt-2 input-group">
                <label class="flex items-start gap-2 text-[12px] text-slate-700">
                    <input type="checkbox" name="foto_rumah_consent" value="1" {{ old('foto_rumah_consent', false) ? 'checked' : '' }}
                        class="mt-0.5 w-4 h-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-200">
                    <span>Saya menyetujukan pengambilan dan penyimpanan foto rumah untuk keperluan administrasi pondok pesantren. Foto hanya dapat dilihat oleh admin.</span>
                </label>
                @error('foto_rumah_consent')
                    <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="mt-5 flex gap-3">
                <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover-lift shadow-lg transform transition-smooth flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Perbarui Santri
                </button>
                <a href="{{ route('santri.index', $filterKembali) }}" class="flex-1 bg-slate-200 text-slate-700 px-5 py-2.5 rounded-lg font-semibold text-sm hover-lift shadow-sm transform transition-smooth flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Batal
                </a>
            </div>

            <!-- Info Box -->
            <div class="mt-5 p-3 bg-emerald-50 border-l-4 border-emerald-400 rounded-lg">
                <p class="text-[12px] text-emerald-700">
                    <strong>Tips:</strong> Lengkapi data alamat dan WhatsApp untuk memudahkan kontak darurat dan administrasi pondok. Foto rumah wajib mendapat persetujuan dan hanya dapat dilihat admin.
                </p>
            </div>
        </form>
    </div>

    <div id="logoutModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[1010]">
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
