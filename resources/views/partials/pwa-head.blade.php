{{-- ============ PWA: Manifest, Ikon, Service Worker ============ --}}
    <link rel="manifest" href="{{ asset('pwa/manifest.css?v=2') }}">
<meta name="theme-color" content="#047857">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="SANTRI.ID">
<link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
<script src="{{ asset('vendor/turbo/turbo.min.js') }}" defer data-turbo-eval="false"></script>
<script data-turbo-eval="false">
    // Turbo MODE OPT-IN: hanya link ber-data-turbo="true" yang dinavigasikan via Turbo.
    // Semua link/form lain tetap berperilaku normal (full-page). Aman dari hijack.
    document.addEventListener('turbo:before-visit', function (e) {
        var fromAttr = e.detail && e.detail.url;
        var t = e.target;
        // turbo:before-visit tidak bawa target elemen; kita cek apakah berasal dari link opt-in
        // Pakai flag yang diset di capture-phase turbo:click.
        if (!window.__turboOptedIn) e.preventDefault();
        window.__turboOptedIn = false;
    });
    document.addEventListener('turbo:click', function (e) {
        var el = e.target.closest('a');
        window.__turboOptedIn = !!(el && el.hasAttribute('data-turbo="true"'));
        if (!window.__turboOptedIn) e.preventDefault();
    }, true);
</script>
<script data-turbo-eval="false">
    // ===== Boot registry Turbo-safe =====
    // Body yg di-swap Turbo di-eval ulang => register fungsi bernama (bukan bind listener
    // document). Registry disimpan di head (eval sekali), jadi TIDAK bertumpuk & tidak
    // memegang referensi elemen lama yang ter-detach. Dipanggil tiap 'turbo:load'
    // (fire di initial load + tiap swap body). Aman untuk chart/filter/drawer.
    window.__pageBoots = {};
    window.__pageBoot = function (name, fn) { window.__pageBoots[name] = fn; };
    window.__firePageBoots = function () {
        for (var k in window.__pageBoots) {
            try { window.__pageBoots[k](); } catch (e) {}
        }
    };
    document.addEventListener('turbo:load', window.__firePageBoots);
</script>
<script>
    if ('serviceWorker' in navigator && location.protocol === 'https:') {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
</script>
