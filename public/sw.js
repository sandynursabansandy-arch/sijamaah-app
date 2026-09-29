/* SIJAMAAH Service Worker — PWA nyata (v3).
 * v3: perbaikan bug titik merah — JANGAN pernah serve cache basi non-HTML
 *     (entri JSON /pesan-user/jumlah dll. dari cache v1 yang kedaluwarsa).
 * - aset statis/ber-hash -> cache-first (immutable)
 * - HTML halaman        -> stale-while-revalidate, HANYA entri cache yang terbukti text/html asli
 * - API/JSON dsb.       -> langsung ke jaringan, tidak di-cache, tidak di-serve basi
 */
var CACHE = 'sijamaah-v3';
var PRECACHE = [
    '/build/assets/app-fNfoOVRA.css',
    '/vendor/turbo/turbo.min.js',
    '/icons/icon-192.png',
    '/icons/apple-touch-icon.png',
    '/pwa/manifest.css',
];

function isAsset(url) {
    return url.pathname.match(/\.(css|js|mjs|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|otf|json)$/);
}

function isHtml(response) {
    var ct = (response.headers.get('content-type') || '').toLowerCase();
    return ct.indexOf('text/html') !== -1;
}

/* Cache/pertahankan HTML hanya bila halaman SIJAMAAH asli (bukan challenge anti-bot / error / JSON). */
function isRealAppHtml(response) {
    if (!response.ok) return false;
    if (!isHtml(response)) return false;
    return response.clone().text().then(function (t) {
        return t.indexOf('__pageBoot') !== -1;
    }).catch(function () { return false; });
}

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE)
            .then(function (cache) { return cache.addAll(PRECACHE); })
            .then(function () { return self.skipWaiting(); })
            .catch(function () { return self.skipWaiting(); })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys()
            .then(function (keys) {
                return Promise.all(
                    keys.filter(function (k) { return k !== CACHE; })
                        .map(function (k) { return caches.delete(k); })
                );
            })
            .then(function () { return self.clients.claim(); })
    );
});

self.addEventListener('fetch', function (event) {
    var req = event.request;
    if (req.method !== 'GET') return;
    var url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    /* ==== ASET STATIS / BER-HASH: cache-first ==== */
    if (isAsset(url)) {
        event.respondWith(
            caches.match(req).then(function (cached) {
                if (cached) return cached;
                return fetch(req).then(function (resp) {
                    if (resp && resp.ok && !isHtml(resp)) {
                        var copy = resp.clone();
                        caches.open(CACHE).then(function (c) { c.put(req, copy); });
                    }
                    return resp;
                });
            })
        );
        return;
    }

    /* ==== API / JSON / favicon dll.: langsung jaringan, tanpa cache, tanpa serve basi ==== */
    var looksPage = (req.mode === 'navigate') ||
                    ((req.headers.get('accept') || '').indexOf('text/html') !== -1) ||
                    ((req.headers.get('sec-purpose') || '').indexOf('prefetch') !== -1);
    if (!looksPage) {
        return; /* biarkan fetch default, tidak disentuh SW */
    }

    /* ==== Halaman / navigasi / warm-up: stale-while-revalidate utk HTML terbukti saja ==== */
    event.respondWith(
        caches.open(CACHE).then(function (cache) {
            return cache.match(req).then(function (cached) {
                var fresh = fetch(req).then(function (resp) {
                    if (resp && resp.ok && isHtml(resp)) {
                        isRealAppHtml(resp).then(function (real) {
                            if (real) {
                                var copy = resp.clone();
                                cache.put(req, copy);
                            }
                        }).catch(function () {});
                    }
                    return resp;
                }).catch(function () { return null; });

                if (cached && isHtml(cached)) {
                    fresh.catch(function () {});
                    return cached;
                }
                return fresh.then(function (resp) {
                    if (resp) return resp;
                    if (cached) return cached;
                    return caches.match('/');
                });
            });
        })
    );
});