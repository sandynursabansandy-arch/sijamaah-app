<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Cepat Presensi</title>
    @vite(['resources/css/app.css'])
    <style>
        .status-btn { transition: all .15s ease; }
        .status-btn:active { transform: scale(0.92); }
        .status-btn.selected-jamaah { background: #059669 !important; color: #fff !important; border-color: #059669 !important; }
        .status-btn.selected-masbuq  { background: #d97706 !important; color: #fff !important; border-color: #d97706 !important; }
        .status-btn.selected-izin    { background: #2563eb !important; color: #fff !important; border-color: #2563eb !important; }
        .status-btn.selected-alfa    { background: #dc2626 !important; color: #fff !important; border-color: #dc2626 !important; }
        @keyframes modalIn { from { opacity:0; transform:scale(.95) translateY(10px);} to {opacity:1; transform:scale(1) translateY(0);} }
        .modal-in { animation: modalIn .2s ease-out; }

        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes barUp { from { opacity: 0; transform: translateY(100%); } to { opacity: 1; transform: translateY(0); } }
        .animate-slide-in { animation: slideIn 0.5s ease-out both; }
        .animate-fade-in { animation: fadeIn 0.6s ease-in both; }
        .santri-card { animation: fadeUp 0.4s ease-out both; }
        .bar-up { animation: barUp 0.45s ease-out 0.3s both; }

        #toast-container { position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 9999; pointer-events: none; }        .toast {
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

        html.nav-open { overflow: hidden !important; overflow-x: clip !important; scrollbar-gutter: stable !important; height: 100% !important; }
        body.nav-open { overflow: hidden !important; overflow-x: clip !important; height: 100% !important; }
        #navPanel { position: fixed !important; inset: 0 auto 0 0 !important; left: -300px !important; width: 280px !important; max-width: 80vw !important; z-index: 2147483647 !important; background: #fff !important; box-shadow: 4px 0 24px rgba(0,0,0,0.25) !important; transition: left 0.4s cubic-bezier(0.4,0,0.2,1) !important; overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; overscroll-behavior: contain !important; }
        #navPanel.nav-open { left: 0 !important; }
        #navOverlay { position: fixed !important; inset: 0 !important; z-index: 998 !important; background: rgba(0,0,0,0.2) !important; -webkit-backdrop-filter: blur(2px) !important; backdrop-filter: blur(2px) !important; display: none !important; }
        #navOverlay.nav-open { display: block !important; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-teal-50 text-slate-800 min-h-screen pb-28">
    <div id="toast-container"></div>
    <div class="max-w-lg mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-4 animate-slide-in">
            <div class="flex items-center gap-2 mb-2">
                <button type="button" onclick="toggleNav()" id="navBtn" class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-teal-600 active:scale-95 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-xl sm:text-2xl md:text-[26px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                    Input Cepat
                </h1>
            </div>
            <p class="text-slate-500 text-xs">Ketuk status untuk tiap santri, lalu simpan</p>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('input-cepat.index') }}" class="bg-white rounded-xl p-3 mb-4 shadow-sm border border-slate-100 grid grid-cols-1 gap-2.5 animate-fade-in">
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()"
                        class="w-full border border-slate-200 py-2 px-2.5 rounded-lg text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600">Sholat</label>
                    <select name="waktu_sholat" onchange="this.form.submit()"
                        class="w-full border border-slate-200 py-2 px-2.5 rounded-lg text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        @foreach(['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] as $w)
                            <option value="{{ $w }}" {{ $waktu === $w ? 'selected' : '' }}>{{ $w }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600">Rayon</label>
                <select name="kamar_id" onchange="this.form.submit()"
                    class="w-full border border-slate-200 py-2 px-2.5 rounded-lg text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                    <option value="all">Semua Rayon</option>
                    @foreach($daftarKamar as $kamar)
                        <option value="{{ $kamar->id }}" {{ (string) $kamarId === (string) $kamar->id ? 'selected' : '' }}>{{ $kamar->nama_kamar }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Daftar santri -->
        <div class="space-y-2.5" id="daftarSantri">
            @forelse($santris as $santri)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-3 santri-card" style="animation-delay: {{ min($loop->index * 40, 400) }}ms" data-santri="{{ $santri->id }}">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="min-w-0">
                            <p class="font-bold text-sm truncate">{{ $santri->nama }}</p>
                            <p class="text-[11px] text-slate-400">{{ $santri->kamar?->nama_kamar ?? '-' }}</p>
                        </div>
                        <button type="button" onclick="clearStatus({{ $santri->id }})" title="Kosongkan"
                            class="shrink-0 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 flex items-center justify-center transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                        @foreach(['Jamaah' => 'jamaah', 'Masbuq' => 'masbuq', 'Izin' => 'izin', 'Alfa' => 'alfa'] as $label => $key)
                            <button type="button"
                                data-status-btn="{{ $santri->id }}" data-value="{{ $label }}"
                                onclick="pickStatus({{ $santri->id }}, '{{ $label }}')"
                                class="status-btn border border-slate-200 rounded-lg py-2 text-[12px] font-bold {{ $santri->status_hari_ini === $label ? 'selected-' . $key : 'bg-slate-50 text-slate-600' }}">
                                {{ $label === 'Jamaah' ? 'Berjamaah' : $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-10 text-center text-slate-400 text-sm animate-fade-in">
                    Tidak ada santri pada filter ini.
                </div>
            @endforelse
        </div>

        @if($errors->any())
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm">{{ $errors->first() }}</div>
        @endif

        <!-- Bar simpan sticky -->
        <div class="fixed bottom-0 inset-x-0 p-3 bg-white/90 backdrop-blur border-t border-slate-200 z-40 bar-up">
            <div class="max-w-lg mx-auto flex items-center gap-2">
                <p class="text-xs text-slate-500 flex-1 min-w-0"><span id="countFilled" class="font-bold text-teal-600">0</span> santri terisi</p>
                <button type="button" onclick="openDeleteModal()"
                    class="shrink-0 bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-lg active:scale-95 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
                <button type="button" onclick="openConfirm()"
                    class="shrink-0 bg-gradient-to-r from-teal-500 to-teal-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg active:scale-95 transition">
                    Simpan
                </button>
            </div>
        </div>

        <!-- Modal konfirmasi simpan -->
        <div id="confirmModal" class="hidden fixed inset-0 z-[1000] items-center justify-center p-4" style="background: rgba(15,23,42,.55);">
            <div class="modal-in bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-teal-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="font-bold text-base mb-1">Simpan presensi?</h3>
                <p id="confirmDetail" class="text-[13px] text-slate-500 mb-4"></p>
                <form id="saveForm" method="POST" action="{{ route('presensi.store') }}">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <input type="hidden" name="waktu_sholat" value="{{ $waktu }}">
                    <div id="hiddenStatuses"></div>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeConfirm()" class="flex-1 bg-slate-100 text-slate-700 py-2.5 rounded-xl font-semibold text-sm">Batal</button>
                        <button type="submit" class="flex-1 bg-gradient-to-r from-teal-500 to-teal-600 text-white py-2.5 rounded-xl font-bold text-sm">Ya, Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal konfirmasi hapus -->
        <div id="deleteModal" class="hidden fixed inset-0 z-[1000] items-center justify-center p-4" style="background: rgba(15,23,42,.55);">
            <div class="modal-in bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="font-bold text-base mb-1 text-red-700">Hapus semua presensi?</h3>
                <p class="text-[13px] text-slate-500 mb-1">Semua data presensi untuk tanggal dan waktu sholat ini akan dihapus permanen.</p>
                <p id="deleteDetail" class="text-[12px] font-bold text-red-600 mb-4"></p>
                <form id="deleteForm" method="POST" action="{{ route('presensi.hapus') }}">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <input type="hidden" name="waktu_sholat" value="{{ $waktu }}">
                    <input type="hidden" name="kamar_id" value="{{ $kamarId }}">
                    <div class="flex gap-2">
                        <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-slate-100 text-slate-700 py-2.5 rounded-xl font-semibold text-sm">Batal</button>
                        <button type="button" onclick="submitDelete()" class="flex-1 bg-red-500 text-white py-2.5 rounded-xl font-bold text-sm">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        @if(session('success'))
            window.__pageBoot('inputCepatInit', function() {
                var container = document.getElementById('toast-container');
                var toast = document.createElement('div');
                toast.className = 'toast success';
                toast.innerHTML = '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ session('success') }}';
                container.appendChild(toast);
                setTimeout(function() { toast.classList.add('fade-out'); }, 2800);
                setTimeout(function() { toast.remove(); }, 3100);
            });
        @endif

        function pickStatus(santriId, status) {
            const key = { Jamaah: 'jamaah', Masbuq: 'masbuq', Izin: 'izin', Alfa: 'alfa' }[status];
            document.querySelectorAll(`[data-status-btn="${santriId}"]`).forEach(btn => {
                btn.classList.remove('selected-jamaah', 'selected-masbuq', 'selected-izin', 'selected-alfa');
                if (!btn.classList.contains('bg-slate-50')) btn.classList.add('bg-slate-50');
            });
            const target = document.querySelector(`[data-status-btn="${santriId}"][data-value="${status}"]`);
            if (target) {
                target.classList.remove('bg-slate-50');
                target.classList.add('selected-' + key);
            }
            updateCount();
        }

        function clearStatus(santriId) {
            document.querySelectorAll(`[data-status-btn="${santriId}"]`).forEach(btn => {
                btn.classList.remove('selected-jamaah', 'selected-masbuq', 'selected-izin', 'selected-alfa');
                btn.classList.add('bg-slate-50');
            });
            updateCount();
        }

        function getSelections() {
            const map = {};
            document.querySelectorAll('[data-status-btn].selected-jamaah, [data-status-btn].selected-masbuq, [data-status-btn].selected-izin, [data-status-btn].selected-alfa').forEach(btn => {
                map[btn.dataset.statusBtn] = btn.dataset.value;
            });
            return map;
        }

        function updateCount() {
            document.getElementById('countFilled').textContent = Object.keys(getSelections()).length;
        }

        function openConfirm() {
            const sel = getSelections();
            if (Object.keys(sel).length === 0) {
                alert('Belum ada status yang dipilih.');
                return;
            }
            document.getElementById('confirmDetail').textContent =
                `${Object.keys(sel).length} santri • ${document.querySelector('[name=waktu_sholat]').value}, ${document.querySelector('[name=tanggal]').value}`;
            const hidden = document.getElementById('hiddenStatuses');
            hidden.innerHTML = '';
            Object.entries(sel).forEach(([id, status]) => {
                hidden.insertAdjacentHTML('beforeend',
                    `<input type="hidden" name="statuses[${id}]" value="${status}">`);
            });
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeConfirm() {
            const modal = document.getElementById('confirmModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openDeleteModal() {
            var waktu = document.querySelector('[name=waktu_sholat]').value;
            var tanggal = document.querySelector('[name=tanggal]').value;
            document.getElementById('deleteDetail').textContent = waktu + ', ' + tanggal;
            var modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            var modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function submitDelete() {
            var form = document.getElementById('deleteForm');
            var data = new FormData(form);
            closeDeleteModal();
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: data
            }).then(function(r) { return r.json(); }).then(function(res) {
                showToast(res.message || 'Berhasil menghapus.', 'success');
                setTimeout(function() { location.reload(); }, 1200);
            }).catch(function() {
                showToast('Gagal menghapus data.', 'error');
            });
        }

        function showToast(msg, type) {
            var container = document.getElementById('toast-container');
            var toast = document.createElement('div');
            toast.className = 'toast ' + (type || 'success');
            var icon = type === 'error'
                ? '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                : '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
            toast.innerHTML = icon + msg;
            container.appendChild(toast);
            setTimeout(function() { toast.classList.add('fade-out'); }, 2800);
            setTimeout(function() { toast.remove(); }, 3100);
        }

        document.getElementById('confirmModal').addEventListener('click', function (e) {
            if (e.target === this) closeConfirm();
        });

        document.getElementById('deleteModal').addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });

        updateCount();
    </script>

    <!-- Nav Drawer -->
    <div id="navOverlay" onclick="closeNav()"></div>
    <aside id="navPanel">
        <div class="p-3 border-b border-slate-100 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-800">Menu Navigasi</p>
            <button onclick="closeNav()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-3 space-y-1">
            @php
                $navItems = [
                    ['Dashboard', 'presensi.index', 'bg-teal-100 text-teal-600', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['Ranking Berjamaah', 'presensi.rankingBerjamaah', 'bg-amber-100 text-amber-600', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    ['Rekap Berjamaah', 'presensi.rekapBerjamaah', 'bg-teal-100 text-teal-600', 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                    ['Ranking Alfa', 'presensi.rankingAlfa', 'bg-red-100 text-red-600', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                    ['Kelola Santri', 'santri.index', 'bg-blue-100 text-blue-600', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['Input Cepat', 'input-cepat.index', 'bg-orange-100 text-orange-600', 'M13 10V3L4 14h7v7l9-11h-7z'],
                    ['Pengajuan Izin', 'izin.index', 'bg-cyan-100 text-cyan-600', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['Riwayat', 'riwayat.index', 'bg-slate-100 text-slate-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['Pengaturan', 'password.change', 'bg-indigo-100 text-indigo-600', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ];
            @endphp
            @foreach ($navItems as $item)
                @continue(in_array($item[1], ['santri.index', 'input-cepat.index']) && !auth()->user()?->canManagePresensi())
                @php $active = request()->routeIs($item[1]); @endphp
                <a href="{{ route($item[1]) }}" onclick="closeNav()"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $active ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'bg-white/20 text-white' : $item[2] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item[3] }}"/></svg>
                    </span>
                    <span class="truncate">{{ $item[0] }}</span>
                </a>
            @endforeach
        </div>
    </aside>
    <script>
        var _navScrollY = 0;
        function openNav() {
            var body = document.body;
            var html = document.documentElement;
            var panel = document.getElementById('navPanel');
            var overlay = document.getElementById('navOverlay');
            _navScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
            panel.classList.add('nav-open');
            overlay.classList.add('nav-open');
            html.classList.add('nav-open');
            body.classList.add('nav-open');
        }
        function closeNav() {
            var body = document.body;
            var html = document.documentElement;
            var panel = document.getElementById('navPanel');
            var overlay = document.getElementById('navOverlay');
            panel.classList.remove('nav-open');
            overlay.classList.remove('nav-open');
            html.classList.remove('nav-open');
            body.classList.remove('nav-open');
            window.scrollTo(0, _navScrollY);
        }
        function toggleNav() {
            var p = document.getElementById('navPanel');
            if (p.classList.contains('nav-open')) { closeNav(); } else { openNav(); }
        }
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeNav(); });
    </script>
</body>
</html>
