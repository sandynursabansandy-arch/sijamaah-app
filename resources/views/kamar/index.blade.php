<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar — SIJAMAAH</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .alert-animate { animation: slideUp 0.5s ease-out; }
        tbody tr { animation: fadeIn 0.3s ease-out both; }
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
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Kelola Kamar</h1>
                <p class="text-[11px] text-slate-400">Tambah, edit, dan hapus kamar asrama</p>
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

        <!-- Form Tambah Kamar -->
        <div class="bg-white rounded-xl shadow-lg p-5 mb-5 animate-slide-up" style="animation-delay:0.05s">
            <h2 class="text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-3">Tambah Kamar Baru</h2>
            <form method="POST" action="{{ route('kamar.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Nama Kamar</label>
                    <input type="text" name="nama_kamar" required placeholder="ex: Al-Mizan"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Wali Kamar</label>
                    <input type="text" name="wali_kamar" placeholder="Nama wali kamar"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Deskripsi</label>
                    <input type="text" name="deskripsi" placeholder="Opsional"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" class="bg-indigo-500 text-white px-5 py-2 rounded-lg font-semibold text-[13px] hover:bg-indigo-600 transition shadow-sm">
                        + Tambah Kamar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Kamar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden animate-slide-up" style="animation-delay:0.1s">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">#</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Nama Kamar</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Wali Kamar</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Deskripsi</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Jml Santri</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($kamars as $i => $kamar)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-4 py-2.5 text-[12px] text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2.5 font-semibold text-[13px]">{{ $kamar->nama_kamar }}</td>
                                <td class="px-4 py-2.5 text-[12px] text-slate-500">{{ $kamar->wali_kamar ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-[12px] text-slate-500">{{ $kamar->deskripsi ?? '-' }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-600">
                                        {{ $kamar->santris_count }} santri
                                    </span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-1">
                                        <button type="button" onclick="openEditModal('{{ $kamar->id }}', '{{ addslashes($kamar->nama_kamar) }}', '{{ addslashes($kamar->wali_kamar ?? '') }}', '{{ addslashes($kamar->deskripsi ?? '') }}')"
                                            class="px-2 py-1 rounded text-[10px] font-semibold bg-amber-100 text-amber-600 hover:bg-amber-200 transition">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('kamar.destroy', $kamar) }}" class="inline"
                                              onsubmit="return confirm('Hapus kamar {{ $kamar->nama_kamar }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded text-[10px] font-semibold bg-red-100 text-red-600 hover:bg-red-200 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">
                                    Belum ada kamar terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[1000]">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Edit Kamar</h3>
                <form method="POST" id="editForm">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">Nama Kamar</label>
                            <input type="text" name="nama_kamar" id="editNama" required
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">Wali Kamar</label>
                            <input type="text" name="wali_kamar" id="editWali"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">Deskripsi</label>
                            <input type="text" name="deskripsi" id="editDeskripsi"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:ring-2 focus:ring-emerald-300 focus:outline-none bg-slate-50">
                        </div>
                    </div>
                    <div class="flex gap-3 mt-5">
                        <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all duration-200">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-500 text-white text-sm font-medium hover:bg-indigo-600 transition-all duration-200 shadow-md shadow-indigo-200">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(id, nama, wali, deskripsi) {
            document.getElementById('editForm').action = '{{ route("kamar.index") }}/' + id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editWali').value = wali;
            document.getElementById('editDeskripsi').value = deskripsi;
            var modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        function closeEditModal() {
            var modal = document.getElementById('editModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    </script>
</body>
</html>