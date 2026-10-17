{{-- ============================================================
     POPUP GLOBAL — layar pop-up konfirmasi & notifikasi.
     Pengganti window.confirm() / window.alert() yang tampilannya
     kecil dan mudah terlewat di Android. Pola & tampilan mengikuti
     popup "Keluar" di header dan popup di halaman Kelas.

     Cara pakai:
       glConfirm(judul, pesan, function () { ... }, opts)
       glAlert(judul, pesan, opts)
       glConfirmForm(form, judul, pesan, opts)  <- untuk onsubmit form

     opts konfirmasi: { ya: 'teks tombol', warna: 'merah'|'hijau',
                        ikon: 'merah'|'hijau'|'indigo'|'amber' }
     opts notifikasi: { jenis: 'sukses'|'error'|'warning'|'info' }

     Partial ini otomatis disertakan lewat partials/app-header.
     Halaman tanpa app-header cukup menambahkan @include di sini.
     ============================================================ --}}
@once
<style>
    .gl-popup[hidden] { display: none; }
    .gl-popup {
        position: fixed; inset: 0; z-index: 2147483647;
        display: flex; align-items: center; justify-content: center;
        padding: 1rem; overflow-y: auto;
    }
    .gl-popup__overlay {
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, 0.45);
        -webkit-backdrop-filter: blur(2px); backdrop-filter: blur(2px);
    }
    .gl-popup__panel {
        position: relative; width: 100%; max-width: 21rem; padding: 20px 18px 16px;
        background: #fff; border-radius: 16px; text-align: center;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
        animation: glPopupMasuk .2s cubic-bezier(.21,1.02,.73,1) both;
    }
    @keyframes glPopupMasuk {
        from { opacity: 0; transform: translateY(14px) scale(.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .gl-popup__ikon {
        width: 52px; height: 52px; margin: 0 auto 12px; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center; flex: none;
    }
    .gl-popup__ikon svg { width: 24px; height: 24px; }
    .gl-popup__judul { font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1.3; }
    .gl-popup__pesan {
        margin-top: 6px; font-size: 12.5px; line-height: 1.6; color: #64748b;
        white-space: pre-line; overflow-wrap: anywhere;
    }
    .gl-popup__aksi { margin-top: 16px; display: flex; gap: 8px; }
    .gl-popup__tombol {
        flex: 1 1 0; min-width: 0; padding: 10px 8px; border-radius: 10px;
        font-size: 13px; font-weight: 700; transition: background-color .15s ease;
    }
    .gl-ikon-hijau { background: #d1fae5; color: #059669; }
    .gl-ikon-merah { background: #fee2e2; color: #dc2626; }
    .gl-ikon-indigo { background: #e0e7ff; color: #4f46e5; }
    .gl-ikon-amber { background: #fef3c7; color: #d97706; }
    .gl-batal { background: #f1f5f9; color: #334155; }
    .gl-batal:active { background: #e2e8f0; }
    .gl-ya { background: #6366f1; color: #fff; }
    .gl-ya:active { background: #4f46e5; }
    .gl-ya.gl-ya-hijau { background: #10b981; }
    .gl-ya.gl-ya-hijau:active { background: #059669; }
    .gl-ya.gl-ya-merah { background: #ef4444; }
    .gl-ya.gl-ya-merah:active { background: #dc2626; }
</style>

<div id="glPopupKonfirmasi" class="gl-popup" hidden>
    <div class="gl-popup__overlay" data-gl-tutup></div>
    <div class="gl-popup__panel" role="dialog" aria-modal="true" aria-labelledby="glPopupKonfirmasiJudul" aria-describedby="glPopupKonfirmasiPesan">
        <div id="glPopupKonfirmasiIkon" class="gl-popup__ikon gl-ikon-indigo"></div>
        <h3 id="glPopupKonfirmasiJudul" class="gl-popup__judul"></h3>
        <p id="glPopupKonfirmasiPesan" class="gl-popup__pesan"></p>
        <div class="gl-popup__aksi">
            <button type="button" class="gl-popup__tombol gl-batal" data-gl-tutup>Batal</button>
            <button type="button" id="glPopupKonfirmasiYa" class="gl-popup__tombol gl-ya">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<div id="glPopupNotif" class="gl-popup" hidden>
    <div class="gl-popup__overlay" data-gl-tutup></div>
    <div class="gl-popup__panel" role="alertdialog" aria-modal="true" aria-labelledby="glPopupNotifJudul" aria-describedby="glPopupNotifPesan">
        <div id="glPopupNotifIkon" class="gl-popup__ikon gl-ikon-indigo"></div>
        <h3 id="glPopupNotifJudul" class="gl-popup__judul"></h3>
        <p id="glPopupNotifPesan" class="gl-popup__pesan"></p>
        <div class="gl-popup__aksi">
            <button type="button" class="gl-popup__tombol gl-ya gl-ya-hijau" data-gl-tutup>Mengerti</button>
        </div>
    </div>
</div>

<script>
(function () {
    var SVG = {
        peringatan: '<svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>',
        centang: '<svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        info: '<svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M12 11v5"/><circle cx="12" cy="12" r="9"/></svg>'
    };

    var terbuka = null;
    var cbYa = null;
    var kembaliFokus = null;

    function el(id) { return document.getElementById(id); }

    function syncGulir() {
        var ada = document.querySelector('.gl-popup:not([hidden])');
        document.body.style.overflow = ada ? 'hidden' : '';
    }

    function tutup() {
        if (!terbuka) return;
        var popup = terbuka;
        terbuka = null;
        cbYa = null;
        popup.hidden = true;
        syncGulir();
        if (kembaliFokus && typeof kembaliFokus.focus === 'function') kembaliFokus.focus();
        kembaliFokus = null;
    }

    function buka(popup) {
        kembaliFokus = document.activeElement;
        popup.hidden = false;
        terbuka = popup;
        syncGulir();
        var tombol = popup.querySelector('button');
        if (tombol) setTimeout(function () { tombol.focus(); }, 40);
    }

    document.addEventListener('click', function (e) {
        var t = (e.target && e.target.closest) ? e.target.closest('[data-gl-tutup], #glPopupKonfirmasiYa') : null;
        if (!t) return;
        if (t.id === 'glPopupKonfirmasiYa') {
            var cb = cbYa;
            tutup();
            if (cb) cb();
        } else {
            tutup();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && terbuka) tutup();
    });

    window.glConfirm = function (judul, pesan, onYa, opts) {
        opts = opts || {};
        var popup = el('glPopupKonfirmasi');
        if (!popup) { if (onYa) onYa(); return; }
        el('glPopupKonfirmasiJudul').textContent = judul || 'Konfirmasi';
        el('glPopupKonfirmasiPesan').textContent = pesan || '';
        var btnYa = el('glPopupKonfirmasiYa');
        btnYa.textContent = opts.ya || 'Ya, Lanjutkan';
        btnYa.className = 'gl-popup__tombol gl-ya' + (opts.warna === 'merah' ? ' gl-ya-merah' : (opts.warna === 'hijau' ? ' gl-ya-hijau' : ''));
        var ikon = el('glPopupKonfirmasiIkon');
        ikon.className = 'gl-popup__ikon ' + (opts.ikon ? 'gl-ikon-' + opts.ikon : 'gl-ikon-indigo');
        ikon.innerHTML = SVG.peringatan;
        cbYa = onYa || null;
        buka(popup);
    };

    window.glAlert = function (judul, pesan, opts) {
        opts = opts || {};
        var popup = el('glPopupNotif');
        if (!popup) return;
        el('glPopupNotifJudul').textContent = judul || 'Perhatian';
        el('glPopupNotifPesan').textContent = pesan || '';
        var jenis = opts.jenis || 'info';
        var ikon = el('glPopupNotifIkon');
        var btn = popup.querySelector('.gl-popup__aksi button');
        if (jenis === 'sukses') {
            ikon.className = 'gl-popup__ikon gl-ikon-hijau';
            ikon.innerHTML = SVG.centang;
            btn.className = 'gl-popup__tombol gl-ya gl-ya-hijau';
        } else if (jenis === 'error') {
            ikon.className = 'gl-popup__ikon gl-ikon-merah';
            ikon.innerHTML = SVG.peringatan;
            btn.className = 'gl-popup__tombol gl-ya gl-ya-merah';
        } else if (jenis === 'warning') {
            ikon.className = 'gl-popup__ikon gl-ikon-amber';
            ikon.innerHTML = SVG.peringatan;
            btn.className = 'gl-popup__tombol gl-ya';
        } else {
            ikon.className = 'gl-popup__ikon gl-ikon-indigo';
            ikon.innerHTML = SVG.info;
            btn.className = 'gl-popup__tombol gl-ya';
        }
        buka(popup);
    };

    window.glConfirmForm = function (form, judul, pesan, opts) {
        if (!form || typeof form.submit !== 'function') return true;
        glConfirm(judul, pesan, function () { form.submit(); }, opts);
        return false;
    };
})();
</script>
@endonce
