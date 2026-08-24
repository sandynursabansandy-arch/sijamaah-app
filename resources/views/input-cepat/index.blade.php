<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Cepat Presensi</title>
    @vite('resources/css/app.css')
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
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen pb-28">
    <div id="toast-container"></div>
    <div class="max-w-lg mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-4 animate-slide-in">
            <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-800 mb-2 text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Dashboard
            </a>
            <h1 class="text-xl sm:text-2xl md:text-[26px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                Input Cepat
            </h1>
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
            <div class="max-w-lg mx-auto flex items-center gap-3">
                <p class="text-xs text-slate-500 flex-1"><span id="countFilled" class="font-bold text-emerald-600">0</span> santri terisi</p>
                <button type="button" onclick="openConfirm()"
                    class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-lg active:scale-95 transition">
                    Simpan
                </button>
            </div>
        </div>

        <!-- Modal konfirmasi -->
        <div id="confirmModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4" style="background: rgba(15,23,42,.55);">
            <div class="modal-in bg-white rounded-2xl shadow-2xl max-w-sm w-full p-5 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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
                        <button type="submit" class="flex-1 bg-emerald-500 text-white py-2.5 rounded-xl font-bold text-sm">Ya, Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', function() {
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

        document.getElementById('confirmModal').addEventListener('click', function (e) {
            if (e.target === this) closeConfirm();
        });

        updateCount();
    </script>
</body>
</html>
