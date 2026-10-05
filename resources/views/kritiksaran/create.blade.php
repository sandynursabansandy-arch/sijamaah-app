<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kritik &amp; Saran &mdash; SANTRI.ID</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.5s ease-out; }
        .animate-slide-in { animation: slideIn 0.5s ease-out both; }
        .alert-animate { animation: slideDown 0.4s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50 text-slate-800 min-h-screen pb-8">
    @include('partials.app-header')
    <div class="max-w-xl mx-auto px-4 sm:px-6 mt-5">

        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5 animate-slide-in">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Kritik &amp; Saran</h1>
                <p class="text-[11px] text-slate-400">Sampaikan masukan Anda untuk kemajuan bersama</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-4 py-2.5 text-sm alert-animate animate-fade-in">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('kritik-saran.store') }}" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4 animate-slide-up">
            @csrf
            <div>
                <label for="pesan" class="block text-xs font-bold uppercase tracking-wide mb-2 text-slate-600">Pesan Kritik &amp; Saran</label>
                <textarea id="pesan" name="pesan" rows="6" required maxlength="1000"
                    placeholder="Tuliskan kritik, masukan, atau saran Anda di sini..."
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg focus:ring-2 focus:ring-emerald-300 text-sm bg-slate-50 focus:outline-none">{{ old('pesan') }}</textarea>
                <div class="mt-1 text-right text-[11px] text-slate-400"><span id="charCount">0</span>/1000</div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-teal-600 text-white py-2.5 rounded-lg font-semibold text-sm shadow-lg hover:opacity-90 transition">
                Kirim Kritik &amp; Saran
            </button>
        </form>
    </div>

    <script>
        (function () {
            var ta = document.getElementById('pesan');
            var out = document.getElementById('charCount');
            if (!ta || !out) return;
            ta.addEventListener('input', function () { out.textContent = ta.value.length; });
        })();
    </script>
</body>
</html>
