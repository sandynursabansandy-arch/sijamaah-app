<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Santri &mdash; SANTRI.ID</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 1.5cm; }
        .cetak-toolbar svg { width: 14px; height: 14px; flex: none; }
        .cetak-judul svg { width: 14px; height: 14px; }
        .print-table { font-size: 10px; }
        .print-table .a-label { font-weight: 600; color: #334155; }
        .print-table th, .print-table td { border: 0.5px solid #e2e8f0; }
        .print-table thead th { font-size: 10px; }
        @media print {
            .cetak-no-print { display: none !important; }
            body { background: white !important; }
            .rounded-xl { border-radius: 0 !important; }
            .shadow-sm { box-shadow: none !important; }
        }
    </style>
</head>
<body>
<div class="min-h-screen bg-gradient-to-b from-slate-100 via-white to-emerald-50 p-4 print:bg-white">
    <div class="max-w-5xl mx-auto">
        <div class="hidden print:block text-center mb-4">
            <h1 class="text-xl font-bold text-slate-800">DAFTAR DATA SANTRI</h1>
            <p class="text-xs text-slate-500">Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}</p>
        </div>

        <div class="flex items-center justify-between mb-4 print:hidden cetak-no-print">
            <a href="{{ route('santri.index', request()->except('kamar_id')) }}"
               class="cetak-toolbar inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg font-semibold text-sm hover:bg-slate-200 transition">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            <div class="cetak-toolbar flex items-center gap-3">
                <div class="relative" id="filterAlamatDropdown">
                    <button type="button" onclick="toggleFilterAlamat()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 rounded-lg font-semibold text-sm text-slate-700 hover:bg-slate-50 transition">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v0"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                        </svg>
                        Filter Alamat: <span id="filterAlamatLabel">Semua</span>
                    </button>
                    <div id="filterAlamatMenu" class="hidden absolute right-0 mt-1 w-72 bg-white rounded-lg shadow-xl border border-slate-200 z-20">
                        <p class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Bagian Alamat (format Tambah Santri)</p>
                        <div class="max-h-72 overflow-y-auto px-1 pb-1">
                            @foreach (\App\Models\Santri::bagianAlamat() as $k => $meta)
                                <label class="flex items-start gap-2 px-2 py-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" class="bagian-alamat mt-0.5" value="{{ $k }}" checked>
                                    <span class="leading-tight">
                                        <span class="block text-[12px] font-semibold text-slate-700">{{ $meta['label'] }}</span>
                                        <span class="block text-[10px] text-slate-400">{{ $meta['contoh'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                            <div class="border-t border-slate-100 mt-1 pt-1">
                                <p class="px-2 pb-0.5 text-[9px] font-bold uppercase tracking-wider text-slate-400">Lainnya</p>
                                <label class="flex items-start gap-2 px-2 py-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" class="bagian-alamat mt-0.5" value="jenis_kelamin" checked>
                                    <span class="leading-tight">
                                        <span class="block text-[12px] font-semibold text-slate-700">Jenis Kelamin</span>
                                        <span class="block text-[10px] text-slate-400">Laki-laki / Perempuan</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 border-t border-slate-100 px-2 py-1.5">
                            <button type="button" onclick="setSemuaBagian(true)" class="flex-1 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-50 rounded py-1">Pilih Semua</button>
                            <button type="button" onclick="setSemuaBagian(false)" class="flex-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 rounded py-1">Hapus Semua</button>
                            <button type="button" onclick="toggleFilterAlamat()" class="flex-1 text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded py-1">Terapkan</button>
                        </div>
                    </div>
                </div>
                <div class="relative" id="filterJkDropdown">
                    <button type="button" onclick="toggleFilterJk()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 rounded-lg font-semibold text-sm text-slate-700 hover:bg-slate-50 transition">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Jenis Kelamin: <span id="filterJkLabel">{{ $jenisKelamin ?: 'Semua' }}</span>
                    </button>
                    <div id="filterJkMenu" class="hidden absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-xl border border-slate-200 py-1 z-20">
                        @php $queryJk = request()->except('filter_jenis_kelamin'); @endphp
                        <a href="{{ route('santri.cetak') }}?{{ http_build_query($queryJk) }}"
                           class="flex items-center gap-2 px-3 py-2 text-[12px] font-semibold {{ $jenisKelamin === '' ? 'text-emerald-700' : 'text-slate-700' }} hover:bg-slate-50">Semua</a>
                        <a href="{{ route('santri.cetak') }}?{{ http_build_query(array_merge($queryJk, ['filter_jenis_kelamin' => 'Laki-laki'])) }}"
                           class="flex items-center gap-2 px-3 py-2 text-[12px] font-semibold {{ $jenisKelamin === 'Laki-laki' ? 'text-emerald-700' : 'text-slate-700' }} hover:bg-slate-50">Laki-laki</a>
                        <a href="{{ route('santri.cetak') }}?{{ http_build_query(array_merge($queryJk, ['filter_jenis_kelamin' => 'Perempuan'])) }}"
                           class="flex items-center gap-2 px-3 py-2 text-[12px] font-semibold {{ $jenisKelamin === 'Perempuan' ? 'text-emerald-700' : 'text-slate-700' }} hover:bg-slate-50">Perempuan</a>
                    </div>
                </div>
                <div class="relative" id="downloadDropdown">
                    <button type="button" onclick="toggleDownloadMenu()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg font-semibold text-sm hover:bg-slate-50 transition">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        Download
                    </button>
                    <div id="downloadMenu" class="hidden absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-xl border border-slate-200 py-1 z-20">
                        <a href="{{ route('santri.export.fdf') . '?' . http_build_query(array_filter(['kamar_id' => request('kamar_id', ''), 'filter_jenis_kelamin' => $jenisKelamin])) }}"
                           download="data_santri_{{ now()->format('Y-m-d_H-i') }}.fdf"
                           class="flex items-center gap-2 px-3 py-2 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download FDF
                        </a>
                        <a href="{{ route('santri.export.excel') . '?' . http_build_query(array_filter(['kamar_id' => request('kamar_id', ''), 'filter_jenis_kelamin' => $jenisKelamin])) }}"
                           download="data_santri_{{ now()->format('Y-m-d_H-i') }}.xlsx"
                           class="flex items-center gap-2 px-3 py-2 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download Excel
                        </a>
                    </div>
                </div>
                <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg font-semibold text-sm hover:bg-slate-50 transition">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9h.01M10 9h.01"/></svg>
                    Cetak Sekarang
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 print:shadow-none print:border-none print:rounded-none">
            <div class="flex items-center gap-2 mb-3 print:mb-2 print:hidden cetak-no-print">
                <div class="cetak-judul w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
                    <svg class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9h.01M10 9h.01"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Data Santri</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse print-table">
                    <thead>
                        <tr class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white">
                            <th class="px-3 py-2 text-center text-xs font-bold">No</th>
                            <th class="px-3 py-2 text-left text-xs font-bold">Nama</th>
                            <th class="px-3 py-2 text-center text-xs font-bold jk-col">Jenis Kelamin</th>
                            <th class="px-3 py-2 text-left text-xs font-bold">Kamar</th>
                            <th class="px-3 py-2 text-left text-xs font-bold">Kelas Pengajian</th>
                            <th class="px-3 py-2 text-left text-xs font-bold">Jenjang Pendidikan</th>
                            <th class="px-3 py-2 text-center text-xs font-bold">Jabatan</th>
                            <th class="px-3 py-2 text-center text-xs font-bold">Pengajar</th>
                            <th class="px-3 py-2 text-left text-xs font-bold">WhatsApp</th>
                            <th class="px-3 py-2 text-left text-xs font-bold">Kabupaten/Kota</th>
                            <th class="px-3 py-2 text-left text-xs font-bold alamat-col">Alamat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($santris as $i => $santri)
                            <tr class="{{ $i % 2 == 0 ? 'bg-slate-50' : 'bg-white' }}">
                                @php
                                    $jk = trim((string) $santri->jenis_kelamin);
                                    $wa = $santri->noWhatsappTelepon();
                                    $kabKota = $santri->kabupatenKotaCetak();
                                @endphp
                                <td class="px-3 py-2 text-center text-[10px] font-semibold text-slate-600">{{ $i + 1 }}</td>
                                <td class="px-3 py-2 text-[10px] font-semibold text-slate-800">{{ $santri->nama }}</td>
                                <td class="px-3 py-2 text-center text-[10px] text-slate-600 jk-col">{{ $jk !== '' ? $jk : $santri->tebakJenisKelamin() }}{{ $jk !== '' ? '' : '*' }}</td>
                                <td class="px-3 py-2 text-[10px] text-slate-600">{{ $santri->kamar->nama_kamar ?? '-' }}</td>
                                <td class="px-3 py-2 text-[10px] text-slate-600">{{ $santri->namaKelasPengajian() }}</td>
                                <td class="px-3 py-2 text-[10px] text-slate-600">{{ $santri->namaKelasSekolah() }}</td>
                                <td class="px-3 py-2 text-center text-[10px] text-slate-600">{{ $santri->jabatan ?: '-' }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold {{ $santri->is_pengajar ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $santri->is_pengajar ? 'Ya' : 'Tidak' }}</span>
                                </td>
                                <td class="px-3 py-2 text-[10px] text-slate-600">{{ $wa !== '' ? $wa : '-' }}</td>
                                <td class="px-3 py-2 text-[10px] text-slate-600">{{ $kabKota !== '' ? $kabKota : '-' }}</td>
                                <td class="px-3 py-2 text-[10px] text-slate-600 alamat-col">
                                    @php $sudahAda = false; @endphp
                                    @foreach ($santri->alamat_bagian as $bagian => $isi)
                                        @if ($isi['nilai'])
                                            @php
                                                // Cetak cukup memakai nama utama bagian, tanpa nama alternatif
                                                // (mis. "Kabupaten/Kota" -> "Kabupaten") supaya tidak tertulis dua kali.
                                                // Untuk Kabupaten labelnya dikosongkan, cukup nama kabupaten/kota saja.
                                                $labelCetak = $bagian === 'kabupaten' ? '' : explode('/', $isi['label'])[0];
                                            @endphp
                                            @if ($sudahAda)
                                                <span class="a-pisah" data-bagian="{{ $bagian }}">, </span>
                                            @endif
                                            @if (! empty($isi['link']))
                                                <a class="a-bagian text-emerald-600 hover:underline" data-bagian="{{ $bagian }}" href="{{ asset('storage/' . $isi['link']) }}">@if($labelCetak)<span class="a-label">{{ $labelCetak }}:</span> @endif{{ $isi['nilai'] }}</a>
                                            @else
                                                <span class="a-bagian" data-bagian="{{ $bagian }}">@if($labelCetak)<span class="a-label">{{ $labelCetak }}:</span> @endif{{ $isi['nilai'] }}</span>
                                            @endif
                                            @php $sudahAda = true; @endphp
                                        @endif
                                    @endforeach
                                    <span class="a-kosong" style="display:none">-</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-8 text-center text-slate-500">Tidak ada data santri.</td></tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
                @php $adaJkTebakan = $santris->contains(fn ($s) => trim((string) $s->jenis_kelamin) === ''); @endphp
                @if($adaJkTebakan)
                    <p class="mt-2 text-[9px] text-slate-400">*) Jenis kelamin bertanda * diperkirakan dari nama.</p>
                @endif
            </div>
    </div>
</div>

<script>
    var KUNCI_BAGIAN = 'cetakAlamatBagianV3';

    function toggleFilterAlamat() {
        var m = document.getElementById('filterAlamatMenu');
        m.classList.toggle('hidden');
    }

    function toggleFilterJk() {
        var m = document.getElementById('filterJkMenu');
        m.classList.toggle('hidden');
    }

    function toggleDownloadMenu() {
        document.getElementById('downloadMenu').classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#filterAlamatDropdown')) {
            document.getElementById('filterAlamatMenu').classList.add('hidden');
        }
        if (!e.target.closest('#filterJkDropdown')) {
            document.getElementById('filterJkMenu').classList.add('hidden');
        }
        if (!e.target.closest('#downloadDropdown')) {
            document.getElementById('downloadMenu').classList.add('hidden');
        }
    });

    function semuaCheckbox() {
        return Array.prototype.slice.call(document.querySelectorAll('.bagian-alamat'));
    }

    function bagianDipilih() {
        return semuaCheckbox()
            .filter(function (c) { return c.checked; })
            .map(function (c) { return c.value; });
    }

    function setSemuaBagian(status) {
        semuaCheckbox().forEach(function (c) { c.checked = status; });
        terapkanBagian();
    }

    function terapkanBagian() {
        var dipilih = bagianDipilih();
        var semua = semuaCheckbox();
        var label = document.getElementById('filterAlamatLabel');

        // Pisahkan bagian alamat dari jenis kelamin supaya kolom
        // Alamat tidak ikut tampil hanya karena jenis kelamin dicentang.
        var bagianAlamat = dipilih.filter(function (k) { return k !== 'jenis_kelamin'; });
        var jkDipilih = dipilih.indexOf('jenis_kelamin') !== -1;

        if (dipilih.length === 0) {
            label.textContent = ' disembunyikan';
        } else if (dipilih.length === semua.length) {
            label.textContent = 'Semua';
        } else {
            label.textContent = dipilih.length + ' bagian';
        }

        document.querySelectorAll('.alamat-col').forEach(function (col) {
            if (col.tagName === 'TH') {
                col.style.display = bagianAlamat.length === 0 ? 'none' : '';
                return;
            }
            var tampil = 0;
            col.querySelectorAll('[data-bagian]').forEach(function (el) {
                var aktif = bagianAlamat.indexOf(el.getAttribute('data-bagian')) !== -1;
                el.style.display = aktif ? '' : 'none';
                if (aktif && el.classList.contains('a-bagian')) tampil++;
            });
            col.style.display = bagianAlamat.length === 0 ? 'none' : '';
            var kosong = col.querySelector('.a-kosong');
            if (kosong) kosong.style.display = (bagianAlamat.length !== 0 && tampil === 0) ? '' : 'none';
        });

        // Kolom Jenis Kelamin: tampil/sembunyi sesuai centangan.
        document.querySelectorAll('.jk-col').forEach(function (col) {
            col.style.display = jkDipilih ? '' : 'none';
        });

        try {
            localStorage.setItem(KUNCI_BAGIAN, JSON.stringify(dipilih));
        } catch (e) {}
    }

    (function initBagian() {
        var tersimpan = null;
        try {
            tersimpan = JSON.parse(localStorage.getItem(KUNCI_BAGIAN) || 'null');
        } catch (e) {}

        if (Array.isArray(tersimpan)) {
            semuaCheckbox().forEach(function (c) {
                c.checked = tersimpan.indexOf(c.value) !== -1;
            });
        }
        semuaCheckbox().forEach(function (c) {
            c.addEventListener('change', terapkanBagian);
        });
        terapkanBagian();
    })();
</script>
</body>
</html>
