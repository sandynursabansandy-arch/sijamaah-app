{{--
    Dua jalur kelas: pengajian dan sekolah.

    Dipakai bersama oleh form Tambah dan Ubah Santri supaya keduanya tidak
    bisa berbeda. Nilai tetap berupa input teks (bukan select) supaya kelas
    yang belum terdaftar tetap bisa diketik dan otomatis didaftarkan sendiri
    ke master kelas — namun sarannya diambil dari master kelas yang sudah ada
    lewat <datalist>, jadi admin tidak perlu mengingat nama kelas yang urut.

    $daftarKelas diisi SantriController::daftarKelasForm(): array
    tahun ajar => ['pengajian' => Collection<KelasMaster>, 'sekolah' => Collection<KelasMaster>]
--}}

@php
    $daftarKelas = $daftarKelas ?? [];
    $namaPengajian = trim((string) old('kelas_pengajian', $santri->kelas_pengajian ?? ''));
    $namaSekolah = trim((string) old('kelas_sekolah', $santri->kelas_sekolah ?? ''));
@endphp

<div class="input-group">
    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Kelas Pengajian</label>
    <input type="text" name="kelas_pengajian" list="daftar-kelas-pengajian" value="{{ $namaPengajian }}"
        placeholder="Contoh: 1, Takhosus I" maxlength="100"
        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
    <datalist id="daftar-kelas-pengajian">
        @foreach ($daftarKelas as $tahun => $isi)
            @foreach ($isi['pengajian'] as $k)
                {{-- Hanya tahun ajar + jenjang: jumlah Santri ikut dihitung
                     akan menambah satu query per kelas, dan ini cuma daftar
                     saran, bukan angka yang perlu akurat di sini. --}}
                <option value="{{ $k->nama }}">{{ $tahun }}</option>
            @endforeach
        @endforeach
    </datalist>
    <p class="text-[10px] text-slate-500 mt-1">Kelas majelis &mdash; tidak naik otomatis, dan tidak semua Santri ikut naik.</p>
    @error('kelas_pengajian')
        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="input-group">
    <label class="block text-[13px] font-semibold text-slate-700 mb-1">Jenjang Pendidikan</label>
    <input type="text" name="kelas_sekolah" list="daftar-kelas-sekolah" value="{{ $namaSekolah }}"
        placeholder="Contoh: 7 SMP, Ibtida 3" maxlength="100"
        class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-smooth text-[13px]">
    <datalist id="daftar-kelas-sekolah">
        @foreach ($daftarKelas as $tahun => $isi)
            @foreach ($isi['sekolah'] as $k)
                <option value="{{ $k->nama }}">{{ $tahun }}</option>
            @endforeach
        @endforeach
    </datalist>
    <p class="text-[10px] text-slate-500 mt-1">Kelas formal &mdash; naik satu tingkat tiap tahun ajar baru.</p>
    @error('kelas_sekolah')
        <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span>
    @enderror
</div>

<style>
    /* Utility di bawah dipakai blade ini tapi belum ada di build CSS, dan
       build CSS produksi tidak bisa di-refresh (file statisanya dilayani
       cache openresty). Ditulis inline supaya tampilannya sama persis di lokal
       dan di hosting. Lihat AGENTS.md bagian "Batasan File Build di Hosting".

       PENTING: col-span-2 hanya boleh berlaku >=768px. Bila didefinisikan
       tanpa media query, paragraf info akan span 2 kolom bahkan di grid
       1-kolom (mobile) dan meluap keluar form. */
    @media (min-width: 768px) { .md\:col-span-2 { grid-column: span 2 / span 2; } }
    .-mt-1 { margin-top: -0.25rem; }
</style>

<p class="text-[10px] text-slate-400 md:col-span-2 -mt-1 leading-relaxed">
    Kedua kelas boleh diisi keduanya, boleh salah satu, boleh kosong. Nama kelas yang diketik
    otomatis terdaftar di <a href="{{ route('kelas.index') }}" class="font-semibold text-emerald-600">Kelas &amp; Tahun Ajar</a>
    supaya bisa dinaikkan sekelas, bukan per Santri.
</p>