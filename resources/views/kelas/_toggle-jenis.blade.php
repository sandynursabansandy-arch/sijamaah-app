{{-- Toggle jenis kelas, dipakai di baris tabel Kelas Sekolah.

     Satu form per baris; tombol yang diklik yang menentukan nilainya. Tidak ada
     select supaya satu klik sudah cukup dan tidak ada langkah "pilih dulu baru
     simpan". Kelas yang salah tebak jenisnya bisa dikoreksi dari sini tanpa
     menyunting nama kelas. --}}
@php
    $isPengajian = $k->isPengajian();
    $clsPengajian = $isPengajian
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-white text-slate-500 hover:bg-slate-200';
    $clsFormal = $isPengajian
        ? 'bg-white text-slate-500 hover:bg-slate-200'
        : 'bg-indigo-100 text-indigo-700';
@endphp

<form method="POST" action="{{ route('kelas.jenis', $k) }}" class="jenis-grup">
    @csrf
    @method('PATCH')
    <button type="submit" name="jenis" value="pengajian"
            @if($isPengajian) disabled @endif
            class="transition {{ $clsPengajian }}">Pengajian</button>
    <button type="submit" name="jenis" value="formal"
            @if(! $isPengajian) disabled @endif
            class="transition border-l border-slate-200 {{ $clsFormal }}">Sekolah</button>
</form>