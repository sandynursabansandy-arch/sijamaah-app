@extends('layouts.masuk')

@section('judul', 'Daftar — SANTRI.ID')

@push('script')
<script>
    (function () {
        try { localStorage.clear(); } catch(e) {}
        try { sessionStorage.clear(); } catch(e) {}
        history.replaceState(null, '', window.location.href);
        window.addEventListener('popstate', function () {
            history.pushState(null, '', window.location.href);
            window.location.href = '{{ route("login") }}';
        });
    })();

    // Toggle password (semua kolom sandi di kartu)
    (function () {
        var tombol = document.querySelectorAll('.sandi-lihat');
        tombol.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var isian = btn.closest('.isian');
                if (!isian) return;
                var terbuka = isian.classList.toggle('sandi-terbuka');
                var input = isian.querySelector('input');
                if (input) input.type = terbuka ? 'text' : 'password';
                btn.setAttribute('aria-pressed', terbuka ? 'true' : 'false');
                btn.setAttribute('aria-label', terbuka ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                if (input) input.focus({ preventScroll: true });
            });
        });
    })();

    // Caps Lock warning
    (function () {
        var inputs = document.querySelectorAll('input[type="password"]');
        inputs.forEach(function (input) {
            var warn = input.closest('.isian')?.querySelector('.waspada');
            if (!warn) return;
            input.addEventListener('keyup', function (e) {
                var hidup = !!(e.getModifierState && e.getModifierState('CapsLock'));
                warn.classList.toggle('aktif', hidup);
            });
        });
    })();

    // Submit state
    (function () {
        var form = document.getElementById('formDaftar');
        var btn = document.getElementById('btnDaftar');
        if (!form || !btn) return;
        form.addEventListener('submit', function () {
            if (btn.dataset.kunci === '1') return;
            btn.dataset.kunci = '1';
            btn.classList.add('proses');
            btn.disabled = true;
        });
    })();

    // PWA install
    (function() {
        var KEY = 'santri_id_pwa_installed';
        function PwaShowBtn(show) {
            var d = document.getElementById('btnPasangPwa');
            if (d) d.classList.toggle('hidden', !show);
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
            try { if (window.localStorage.getItem(KEY) === '1') return true; } catch(e) {}
            return false;
        }
        function markInstalled() { try { window.localStorage.setItem(KEY, '1'); } catch(e) {} PwaShowBtn(false); }
        var deferred = null; PwaShowBtn(false);
        if (isInstalledMode()) markInstalled();
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault(); deferred = e;
            if (!isInstalledMode()) PwaShowBtn(true);
        });
        window.addEventListener('appinstalled', function() { deferred = null; markInstalled(); });
        function tapInstall() {
            if (deferred) {
                deferred.prompt();
                if (deferred.userChoice) {
                    deferred.userChoice.then(function(c) {
                        if (c && c.outcome === 'accepted') { deferred = null; markInstalled(); return; }
                        deferred = null; PwaShowBtn(false);
                    });
                }
            }
        }
        var btn = document.getElementById('btnPasangPwa');
        if (btn) btn.addEventListener('click', function(e) { e.preventDefault(); tapInstall(); });
    })();
</script>
@endpush

@section('konten')
    {{-- Panel kiri: identitas aplikasi (sama seperti halaman masuk) --}}
    <section class="panel-merek">
        <span class="jala" aria-hidden="true"></span>
        <span class="bola bola-1" aria-hidden="true"></span>
        <span class="bola bola-2" aria-hidden="true"></span>
        <span class="bola bola-3" aria-hidden="true"></span>
        <div class="tabur" id="tabur" aria-hidden="true"></div>

        <div class="merek">
            <span class="mark">
                <img src="{{ asset('images/nw.png') }}" alt="Logo SANTRI.ID" width="44" height="44">
            </span>
            <div>
                <p class="nama">SANTRI.ID</p>
                <div class="sub">Sistem Presensi Sholat Berjamaah Pondok Pesantren</div>
            </div>
        </div>

        <div class="isi-merek">
            <span class="label-atas"><span class="titik"></span> Ruang Pengelola</span>
            <h1>Kelola presensi sholat <em>satu pintu</em> dan terlacak.</h1>
            <p class="terang">
                Sistem ini dirancang khusus untuk mengelola kehadiran sholat berjamaah,
                data santri, dan komunikasi WhatsApp terintegrasi di lingkungan pondok pesantren.
            </p>

            <ul class="fitur">
                <li>
                    <span class="kotak" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <span>Pencatatan presensi harian dengan diagram per santri.</span>
                </li>
                <li>
                    <span class="kotak" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <span>Data santri terintegrasi WhatsApp untuk notifikasi penting.</span>
                </li>
                <li>
                    <span class="kotak" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <span>Riwayat perubahan tersimpan otomatis sebagai catatan pemeriksaan.</span>
                </li>
            </ul>
        </div>

        <div class="kaki-merek">
            <div class="meta">
                <span>SANTRI.ID</span>
                <span class="pisah"></span>
                <span>&copy; {{ date('Y') }} Pondok Pesantren Digital</span>
            </div>
        </div>
    </section>

    {{-- Panel kanan: kartu formulir daftar --}}
    <section class="panel-formulir">
        <span class="kabut kabut-1" aria-hidden="true"></span>
        <span class="kabut kabut-2" aria-hidden="true"></span>

        <div class="kartu" id="kartu">
            <div class="kartu-kepala" style="--t:0">
                <div class="lencana" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M19 8v6M22 11h-6"/>
                    </svg>
                </div>
                <h2>Buat Akun</h2>
                <p>Daftar untuk mengakses sistem presensi SANTRI.ID.</p>
            </div>

            @if ($errors->any())
                <div class="pesan goyang" role="alert" style="--t:1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.2v.1"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="formDaftar" autocomplete="off">
                @csrf

                <div class="isian" style="--t:2">
                    <input type="text" name="name" id="nama" value="{{ old('name') }}"
                           placeholder=" " required autocomplete="name"
                           @if ($errors->any()) autofocus @endif>
                    <label class="label" for="nama">Nama Lengkap</label>
                    <svg class="ikon-kiri" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>
                    </svg>
                </div>

                <div class="isian" style="--t:3">
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           placeholder=" " required autocomplete="email"
                           autocapitalize="none" spellcheck="false">
                    <label class="label" for="email">Email</label>
                    <svg class="ikon-kiri" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.6 7 8.4 6 8.4-6"/>
                    </svg>
                </div>

                <div class="isian sandi" style="--t:4">
                    <input type="password" name="password" id="sandi" placeholder=" " required
                           minlength="6" autocomplete="new-password" data-sandi>
                    <label class="label" for="sandi">Kata Sandi</label>
                    <svg class="ikon-kiri" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="10" rx="2.5"/><path d="M8 10V7.5a4 4 0 0 1 8 0V10"/>
                    </svg>
                    <button type="button" class="sandi-lihat" aria-label="Tampilkan kata sandi" aria-pressed="false">
                        <svg class="ikon-buka" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2.5 12S6 5.8 12 5.8 21.5 12 21.5 12 18 18.2 12 18.2 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3.1"/>
                        </svg>
                        <svg class="ikon-tutup" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 4.5 20 19.5"/><path d="M9.6 6.2A9.6 9.6 0 0 1 12 5.9c6 0 9.5 6.1 9.5 6.1a16 16 0 0 1-3 3.6"/><path d="M6.3 8.1A16 16 0 0 0 2.5 12S6 18.1 12 18.1a9.4 9.4 0 0 0 3.3-.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                        </svg>
                    </button>
                    <span class="waspada" data-kunci>Kapital aktif — kata sandi biasanya memakai huruf kecil.</span>
                </div>

                <div class="isian sandi" style="--t:5">
                    <input type="password" name="password_confirmation" id="sandi2" placeholder=" " required
                           minlength="6" autocomplete="new-password">
                    <label class="label" for="sandi2">Ulangi Kata Sandi</label>
                    <svg class="ikon-kiri" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <button type="button" class="sandi-lihat" aria-label="Tampilkan kata sandi" aria-pressed="false">
                        <svg class="ikon-buka" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2.5 12S6 5.8 12 5.8 21.5 12 21.5 12 18 18.2 12 18.2 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3.1"/>
                        </svg>
                        <svg class="ikon-tutup" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 4.5 20 19.5"/><path d="M9.6 6.2A9.6 9.6 0 0 1 12 5.9c6 0 9.5 6.1 9.5 6.1a16 16 0 0 1-3 3.6"/><path d="M6.3 8.1A16 16 0 0 0 2.5 12S6 18.1 12 18.1a9.4 9.4 0 0 0 3.3-.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                        </svg>
                    </button>
                </div>

                <button class="kirim" type="submit" id="btnDaftar" style="--t:6">
                    <span class="teks">Daftar Sekarang</span>
                    <svg class="panah" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4.5 12h14M13 6.5 18.5 12 13 17.5"/>
                    </svg>
                    <span class="lingkaran" aria-hidden="true"></span>
                </button>
            </form>

            <div class="pisah-atau" style="--t:7">atau</div>

            <div style="--t:8">
                <div style="display: flex; justify-content: center; gap: 1rem; margin-bottom: .5rem;">
                    <a href="{{ route('login') }}" class="text-xs font-medium text-teal-600 hover:text-teal-700 transition">
                        Sudah Punya Akun? Masuk
                    </a>
                </div>
            </div>

            <div class="kaki-kartu" style="--t:9">
                <span class="lencana-aman">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="10" rx="2.5"/><path d="M8 10V7.5a4 4 0 0 1 8 0V10"/>
                    </svg>
                    Akses terbatas petugas
                </span>
                &middot; SANTRI.ID &copy; {{ date('Y') }}
                <div class="mt-2">
                    <a href="javascript:void(0)" id="btnPasangPwa" class="pasang-btn hidden">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m7 7l-7 7-7-7"/>
                        </svg>
                        <span>Pasang Aplikasi PWA</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
