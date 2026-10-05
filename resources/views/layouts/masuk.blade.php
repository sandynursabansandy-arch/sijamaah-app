<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Masuk — SANTRI.ID')</title>
    <meta name="description" content="Sistem manajemen presensi sholat berjamaah Pondok Pesantren — kelola kehadiran, data santri, dan komunikasi WhatsApp terintegrasi.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --teal-950: #04211f;
            --teal-900: #0b3b39;
            --teal-800: #0f4f4c;
            --teal-700: #0f766e;
            --teal-600: #0d9488;
            --teal-500: #14b8a6;
            --teal-400: #2dd4bf;
            --teal-300: #5eead4;
            --emas: #f0b429;
            --emas-2: #f5c96c;
            --ink: #0b1b2a;
            --ink-2: #33475b;
            --muted: #647b8c;
            --line: rgba(15, 23, 42, .09);
            --lembut: cubic-bezier(.22, 1, .36, 1);
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        a { color: var(--teal-700); text-decoration: none; }
        button { font-family: inherit; }
        .hidden { display: none !important; }

        .panggung {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(0, .98fr);
        }
        .panggung > section { position: relative; z-index: 1; }

        /* PANEL KIRI — latar bergerak aurora */
        .panel-merek {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(1.6rem, 3.4vw, 3rem) clamp(1.6rem, 3.6vw, 3.4rem);
            color: #eafffb;
            background:
                radial-gradient(120% 90% at 8% 0%, #0f5c57 0%, transparent 55%),
                radial-gradient(90% 80% at 100% 100%, #0d7f74 0%, transparent 58%),
                linear-gradient(155deg, var(--teal-950) 0%, var(--teal-900) 48%, #0a4a45 100%);
            isolation: isolate;
        }

        /* jala grid tipis — tekstur "lembar data" */
        .jala {
            position: absolute; inset: -20%; z-index: 0;
            background-image:
                linear-gradient(to right, rgba(94, 234, 212, .09) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(94, 234, 212, .09) 1px, transparent 1px);
            background-size: 62px 62px;
            mask-image: radial-gradient(70% 70% at 50% 45%, #000 20%, transparent 78%);
            -webkit-mask-image: radial-gradient(70% 70% at 50% 45%, #000 20%, transparent 78%);
            animation: geser-jala 34s linear infinite;
        }
        @keyframes geser-jala {
            from { transform: translate3d(0, 0, 0); }
            to   { transform: translate3d(-62px, -62px, 0); }
        }

        /* aurora: tiga gumpalan cahaya besar yang bergerak pelan */
        .bola {
            position: absolute; border-radius: 50%;
            filter: blur(70px); opacity: .5; z-index: 0;
            will-change: transform;
        }
        .bola-1 { width: 42vmax; height: 42vmax; top: -16vmax; left: -12vmax; background: radial-gradient(circle, #14b8a6 0%, transparent 68%); animation: hanyut-1 24s ease-in-out infinite alternate; }
        .bola-2 { width: 36vmax; height: 36vmax; bottom: -14vmax; right: -10vmax; background: radial-gradient(circle, #0ea5a0 0%, transparent 70%); animation: hanyut-2 29s ease-in-out infinite alternate; }
        .bola-3 { width: 22vmax; height: 22vmax; top: 38%; right: 12%; background: radial-gradient(circle, #f0b429 0%, transparent 72%); opacity: .16; animation: hanyut-3 21s ease-in-out infinite alternate; }
        @keyframes hanyut-1 { from { transform: translate3d(0,0,0) scale(1); } to { transform: translate3d(7vmax, 5vmax, 0) scale(1.14); } }
        @keyframes hanyut-2 { from { transform: translate3d(0,0,0) scale(1.06); } to { transform: translate3d(-6vmax, -4vmax, 0) scale(.92); } }
        @keyframes hanyut-3 { from { transform: translate3d(0,0,0) scale(.9); } to { transform: translate3d(-5vmax, 6vmax, 0) scale(1.2); } }

        /* partikel kecil — dibuat oleh JS */
        .tabur { position: absolute; inset: 0; z-index: 0; pointer-events: none; }
        .butir {
            position: absolute; bottom: -12px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(153, 246, 228, .95) 0%, rgba(153, 246, 228, 0) 70%);
            animation: naik linear infinite;
            will-change: transform, opacity;
        }
        @keyframes naik {
            0%   { opacity: 0; transform: translate3d(0, 0, 0) scale(.6); }
            12%  { opacity: .8; }
            85%  { opacity: .45; }
            100% { opacity: 0; transform: translate3d(28px, -108vh, 0) scale(1.1); }
        }

        /* garis emas tipis di sisi panel kanan */
        .panel-merek::after {
            content: ''; position: absolute; top: 0; bottom: 0; right: 0; width: 2px;
            background: linear-gradient(to bottom, transparent, rgba(240, 180, 41, .55), rgba(45, 212, 191, .35), transparent);
            z-index: 2;
        }

        .merek { position: relative; z-index: 3; display: flex; gap: .95rem; align-items: center; }
        .merek .mark {
            width: 54px; height: 54px; flex: none; padding: 5px;
            border-radius: 15px; background: #fff;
            box-shadow: 0 10px 26px -12px rgba(0, 0, 0, .7);
            animation: denyut 6s ease-in-out infinite;
        }
        .merek .mark img { width: 100%; height: 100%; object-fit: contain; display: block; }
        @keyframes denyut {
            0%, 100% { box-shadow: 0 10px 26px -12px rgba(0, 0, 0, .7), 0 0 0 0 rgba(45, 212, 191, .35); }
            50%      { box-shadow: 0 10px 26px -12px rgba(0, 0, 0, .7), 0 0 0 9px rgba(45, 212, 191, 0); }
        }
        .merek .nama {
            font-family: 'Playfair Display', serif; font-size: 1.6rem; line-height: 1.05;
            letter-spacing: .1em; margin: 0; color: #fff;
        }
        .merek .sub { font-size: .78rem; color: var(--teal-300); margin-top: .22rem; letter-spacing: .01em; }

        .isi-merek { position: relative; z-index: 3; max-width: 33rem; margin: clamp(2rem, 5vh, 3.4rem) 0; }
        .isi-merek .label-atas {
            display: inline-flex; align-items: center; gap: .5rem;
            font-size: .7rem; font-weight: 600; letter-spacing: .16em; text-transform: uppercase;
            color: var(--teal-300);
            padding: .38rem .85rem .38rem .5rem; border-radius: 999px;
            background: rgba(94, 234, 212, .1);
            box-shadow: inset 0 0 0 1px rgba(94, 234, 212, .28);
        }
        .isi-merek .label-atas .titik {
            width: 7px; height: 7px; border-radius: 50%; background: var(--teal-400);
            box-shadow: 0 0 0 0 rgba(45, 212, 191, .6);
            animation: kedip 2.2s ease-out infinite;
        }
        @keyframes kedip {
            0%   { box-shadow: 0 0 0 0 rgba(45, 212, 191, .55); }
            70%  { box-shadow: 0 0 0 8px rgba(45, 212, 191, 0); }
            100% { box-shadow: 0 0 0 0 rgba(45, 212, 191, 0); }
        }
        .isi-merek h1 {
            font-family: 'Playfair Display', serif; font-weight: 700;
            font-size: clamp(2.1rem, 3.6vw, 3.05rem); line-height: 1.14;
            margin: 1.1rem 0 0; color: #fff; letter-spacing: -.01em;
            text-wrap: balance;
        }
        .isi-merek h1 em {
            font-style: normal;
            background: linear-gradient(100deg, var(--teal-300), #ffffff 45%, var(--emas));
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .isi-merek p.terang { margin: 1rem 0 0; color: #c8ece5; font-size: clamp(.94rem, 1.05vw, 1.02rem); line-height: 1.65; max-width: 30rem; }

        .fitur { list-style: none; margin: 1.9rem 0 0; padding: 0; display: grid; gap: .7rem; }
        .fitur li {
            display: flex; align-items: flex-start; gap: .75rem;
            font-size: .88rem; color: #dcf7f2; line-height: 1.5;
        }
        .fitur .kotak {
            flex: none; width: 26px; height: 26px; border-radius: 9px;
            display: grid; place-items: center; margin-top: .05rem;
            background: linear-gradient(150deg, rgba(45, 212, 191, .28), rgba(45, 212, 191, .08));
            box-shadow: inset 0 0 0 1px rgba(94, 234, 212, .32);
            color: var(--teal-300);
            transition: transform .3s var(--lembut), background-color .3s ease;
        }
        .fitur li:hover .kotak { transform: translateY(-2px) rotate(-6deg) scale(1.06); background: linear-gradient(150deg, rgba(45, 212, 191, .42), rgba(45, 212, 191, .12)); }
        .fitur .kotak svg { width: 14px; height: 14px; }

        .kaki-merek { position: relative; z-index: 3; }
        .kaki-merek .meta { display: flex; align-items: center; gap: .7rem; flex-wrap: wrap; font-size: .76rem; color: #9fd8ce; }
        .kaki-merek .pisah { width: 4px; height: 4px; border-radius: 50%; background: rgba(159, 216, 206, .55); }
        .kaki-merek .mitra { display: flex; align-items: center; gap: .55rem; margin-top: 1rem; flex-wrap: wrap; }
        .kaki-merek .mitra .sampo {
            height: 26px; padding: 4px 10px; border-radius: 9px; background: rgba(255, 255, 255, .92);
            box-shadow: 0 6px 18px -12px rgba(0, 0, 0, .8);
            display: grid; place-items: center;
            transition: transform .3s var(--lembut), box-shadow .3s ease;
        }
        .kaki-merek .mitra .sampo:hover { transform: translateY(-3px); box-shadow: 0 12px 24px -14px rgba(0, 0, 0, .9); }
        .kaki-merek .mitra img { height: 18px; width: auto; display: block; }

        /* PANEL KANAN — latar formulir */
        .panel-formulir {
            position: relative;
            display: grid;
            place-items: center;
            padding: clamp(1.4rem, 3.5vw, 3rem) clamp(1.1rem, 3vw, 2.6rem);
            background:
                radial-gradient(80% 60% at 100% 0%, rgba(20, 184, 166, .12) 0%, transparent 60%),
                radial-gradient(70% 60% at 0% 100%, rgba(240, 180, 41, .1) 0%, transparent 62%),
                linear-gradient(160deg, #f7fbfa 0%, #eef5f4 55%, #e6efee 100%);
            overflow: hidden;
        }
        .panel-formulir .kabut {
            position: absolute; border-radius: 50%; filter: blur(60px);
            pointer-events: none; z-index: 0;
            will-change: transform;
        }
        .kabut-1 { width: 30rem; height: 30rem; top: -12rem; right: -10rem; background: rgba(20, 184, 166, .2); animation: kabut-a 26s ease-in-out infinite alternate; }
        .kabut-2 { width: 26rem; height: 26rem; bottom: -11rem; left: -9rem; background: rgba(240, 180, 41, .16); animation: kabut-b 31s ease-in-out infinite alternate; }
        @keyframes kabut-a { from { transform: translate3d(0,0,0) scale(1); } to { transform: translate3d(-4rem, 3rem, 0) scale(1.15); } }
        @keyframes kabut-b { from { transform: translate3d(0,0,0) scale(1.08); } to { transform: translate3d(3rem, -3rem, 0) scale(.92); } }

        .kartu {
            position: relative;
            z-index: 1;
            width: min(27rem, 100%);
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(18px) saturate(150%);
            -webkit-backdrop-filter: blur(18px) saturate(150%);
            border: 1px solid rgba(255, 255, 255, .8);
            outline: 1px solid rgba(15, 23, 42, .05);
            border-radius: 26px;
            padding: clamp(1.6rem, 3vw, 2.3rem);
            box-shadow:
                0 1px 0 rgba(255, 255, 255, .9) inset,
                0 30px 70px -32px rgba(4, 33, 31, .38),
                0 12px 28px -20px rgba(4, 33, 31, .28);
            overflow: hidden;
        }
        .kartu::before {
            content: ''; position: absolute; left: 0; right: 0; top: 0; height: 3px;
            background: linear-gradient(90deg, var(--teal-500), var(--teal-300) 40%, var(--emas) 100%);
            transform-origin: left center;
            animation: sapu 4.5s var(--lembut) infinite;
        }
        @keyframes sapu {
            0%, 100% { transform: scaleX(.35); opacity: .7; }
            50%      { transform: scaleX(1); opacity: 1; }
        }
        .kartu::after {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: radial-gradient(240px circle at var(--mx, 50%) var(--my, 0%), rgba(45, 212, 191, .13), transparent 70%);
            opacity: 0; transition: opacity .4s ease;
        }
        .kartu:hover::after { opacity: 1; }

        .kartu-kepala { text-align: center; margin-bottom: 1.5rem; }
        .kartu-kepala .lencana {
            width: 56px; height: 56px; margin: 0 auto .9rem;
            border-radius: 18px; display: grid; place-items: center;
            color: #fff;
            background: linear-gradient(145deg, var(--teal-500), var(--teal-700) 60%, var(--teal-800));
            box-shadow: 0 14px 26px -14px rgba(13, 148, 136, .9), inset 0 1px 0 rgba(255, 255, 255, .35);
        }
        .kartu-kepala .lencana svg { width: 26px; height: 26px; }
        .kartu-kepala h2 {
            font-family: 'Playfair Display', serif; font-size: 1.75rem; margin: 0;
            letter-spacing: -.01em; color: var(--ink);
        }
        .kartu-kepala p { margin: .4rem 0 0; font-size: .86rem; color: var(--muted); line-height: 1.55; }

        /* --- pesan galat --- */
        .pesan {
            display: flex; gap: .65rem; align-items: flex-start;
            border-radius: 14px; padding: .75rem .85rem; font-size: .83rem; line-height: 1.5;
            border: 1px solid var(--garis-galat); background: #fef2f2; color: #991b1b;
            margin-bottom: 1.1rem;
        }
        .pesan svg { width: 18px; height: 18px; flex: none; margin-top: .1rem; }
        .pesan.goyang > span { display: block; animation: getar .55s cubic-bezier(.36, .07, .19, .97) .32s both; }
        @keyframes getar {
            10%, 90% { transform: translate3d(-2px, 0, 0); }
            20%, 80% { transform: translate3d(4px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-7px, 0, 0); }
            40%, 60% { transform: translate3d(7px, 0, 0); }
        }

        /* --- bidang isian: label melayang + ikon yang menyingkrak --- */
        .isian { position: relative; margin-bottom: 1.05rem; }
        .isian .ikon-kiri {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; color: var(--muted); pointer-events: none;
            transition: opacity .22s ease, transform .22s var(--lembut), color .25s ease;
        }
        .isian input {
            width: 100%; font: inherit; font-size: .93rem; color: var(--ink);
            padding: 1.42rem 1rem .62rem 2.75rem;
            border: 1px solid var(--line); border-radius: 14px; background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            transition: border-color .25s ease, box-shadow .25s ease, background-color .25s ease;
            -webkit-appearance: none; appearance: none;
        }
        .isian.sandi input { padding-right: 3.1rem; }
        .isian input::placeholder { color: transparent; }
        .isian input:hover { border-color: rgba(13, 148, 136, .35); }
        .isian input:focus {
            outline: none; border-color: var(--teal-500);
            box-shadow: 0 0 0 4px rgba(20, 184, 166, .14), 0 6px 16px -10px rgba(13, 148, 136, .6);
        }
        /* Ikon tetap terlihat saat kolom difokus atau sudah berisi */
        .isian input:focus ~ .ikon-kiri,
        .isian input:not(:placeholder-shown) ~ .ikon-kiri {
            opacity: 1;
            transform: translateY(-50%) scale(1);
            color: var(--teal-600);
        }
        .isian .label {
            position: absolute; left: 2.75rem; top: 1.02rem; pointer-events: none;
            font-size: .9rem; color: var(--muted);
            transform-origin: left top;
            transition: transform .22s var(--lembut), color .22s ease;
        }
        .isian input:focus ~ .label,
        .isian input:not(:placeholder-shown) ~ .label {
            transform: translateY(-.72rem) scale(.76);
            color: var(--teal-700);
        }
        .isian .sandi-lihat {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            width: 34px; height: 34px; display: grid; place-items: center;
            border: 0; border-radius: 10px; background: transparent; color: var(--muted);
            cursor: pointer; transition: color .2s ease, background-color .2s ease, transform .2s var(--lembut);
        }
        .isian .sandi-lihat:hover { color: var(--teal-700); background: rgba(20, 184, 166, .1); }
        .isian .sandi-lihat:active { transform: translateY(-50%) scale(.9); }
        .isian .sandi-lihat svg { width: 18px; height: 18px; }
        .isian .sandi-lihat .ikon-tutup { display: none; }
        .isian.sandi-terbuka .sandi-lihat .ikon-buka { display: none; }
        .isian.sandi-terbuka .sandi-lihat .ikon-tutup { display: block; }
        .waspada {
            display: block; margin-top: .4rem; font-size: .72rem; color: var(--emas);
            opacity: 0; max-height: 0; overflow: hidden;
            transition: opacity .25s ease, max-height .25s ease;
        }
        .waspada.aktif { opacity: 1; max-height: 2rem; }

        /* --- ingat saya --- */
        .ingat {
            position: relative;
            display: inline-flex; align-items: center; gap: .55rem;
            font-size: .85rem; color: var(--ink-2); cursor: pointer; user-select: none;
            margin: .1rem 0 1.25rem;
        }
        .ingat input { position: absolute; opacity: 0; width: 0; height: 0; }
        .ingat .kotak {
            width: 20px; height: 20px; flex: none; border-radius: 7px;
            border: 1px solid rgba(15, 23, 42, .22); background: #fff;
            display: grid; place-items: center; color: #fff;
            transition: background-color .22s ease, border-color .22s ease, transform .22s var(--lembut);
        }
        .ingat .kotak svg { width: 12px; height: 12px; transform: scale(0); transition: transform .26s var(--lembut); }
        .ingat:hover .kotak { border-color: var(--teal-500); background: rgba(20, 184, 166, .08); }
        .ingat input:checked + .kotak { background: linear-gradient(145deg, var(--teal-500), var(--teal-700)); border-color: var(--teal-700); }
        .ingat input:checked + .kotak svg { transform: scale(1); }
        .ingat input:focus-visible + .kotak { box-shadow: 0 0 0 4px rgba(20, 184, 166, .18); }

        /* --- tombol kirim --- */
        .kirim {
            position: relative; overflow: hidden;
            width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: .55rem;
            font: inherit; font-size: .93rem; font-weight: 600; letter-spacing: .01em;
            color: #fff; cursor: pointer; border: 0; border-radius: 14px;
            padding: .88rem 1.2rem;
            background: linear-gradient(135deg, var(--teal-600) 0%, var(--teal-700) 48%, var(--teal-800) 100%);
            box-shadow: 0 14px 28px -14px rgba(15, 118, 110, .95), inset 0 1px 0 rgba(255, 255, 255, .22);
            transition: transform .22s var(--lembut), box-shadow .22s ease, filter .22s ease;
        }
        .kirim:hover { transform: translateY(-2px); box-shadow: 0 20px 34px -14px rgba(15, 118, 110, 1), inset 0 1px 0 rgba(255, 255, 255, .3); filter: saturate(1.1); }
        .kirim:active { transform: translateY(0); }
        .kirim::before {
            content: ''; position: absolute; top: 0; bottom: 0; width: 45%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .38), transparent);
            transform: translateX(-160%) skewX(-18deg);
            animation: sapuan 3.4s var(--lembut) infinite;
            pointer-events: none;
        }
        @keyframes sapuan {
            0%, 62% { transform: translateX(-160%) skewX(-18deg); }
            86%, 100% { transform: translateX(320%) skewX(-18deg); }
        }
        .kirim svg.panah { width: 17px; height: 17px; transition: transform .3s var(--lembut); }
        .kirim:hover svg.panah { transform: translateX(4px); }
        .kirim .lingkaran {
            display: none; width: 17px; height: 17px; border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, .35); border-top-color: #fff;
            animation: muter .7s linear infinite;
        }
        @keyframes muter { to { transform: rotate(360deg); } }
        .kirim.proses { cursor: progress; filter: saturate(.9); }
        .kirim.proses svg.panah { display: none; }
        .kirim.proses .lingkaran { display: block; }
        .kirim.proses .teks { opacity: .85; }

        /* --- pemisah + tautan kembali --- */
        .pisah-atau { display: flex; align-items: center; gap: .8rem; margin: 1.35rem 0 1.05rem; color: #9bb0bd; font-size: .74rem; letter-spacing: .1em; text-transform: uppercase; }
        .pisah-atau::before, .pisah-atau::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, transparent, var(--line), transparent); }
        .kembali {
            display: flex; align-items: center; justify-content: center; gap: .45rem;
            font-size: .86rem; font-weight: 500; color: var(--ink-2);
            padding: .62rem .8rem; border-radius: 12px;
            border: 1px solid transparent;
            transition: color .2s ease, background-color .2s ease, border-color .2s ease, transform .2s var(--lembut);
        }
        .kembali svg { width: 15px; height: 15px; transition: transform .3s var(--lembut); }
        .kembali:hover { color: var(--teal-700); background: rgba(20, 184, 166, .08); border-color: rgba(13, 148, 136, .2); }
        .kembali:hover svg { transform: translateX(-4px); }

        .kaki-kartu { margin-top: 1.5rem; text-align: center; font-size: .74rem; color: #8ba1b0; line-height: 1.6; }
        .kaki-kartu .lencana-aman { display: inline-flex; align-items: center; gap: .35rem; color: var(--teal-700); font-weight: 500; }
        .kaki-kartu .lencana-aman svg { width: 13px; height: 13px; }

        /* --- cari akun panel --- */
        .panel-cari {
            margin: 1.1rem 0 0; border-radius: 14px; border: 1px solid var(--line);
            background: #f8fafc;
        }
        .panel-cari input {
            width: 100%; font: inherit; font-size: .9rem; color: var(--ink);
            padding: .55rem .85rem;
            border: 0; border-radius: 12px; background: transparent;
            outline: none; transition: box-shadow .2s ease;
        }
        .panel-cari input:focus { box-shadow: 0 0 0 3px rgba(20, 184, 166, .14); }
        .hasil-cari { margin-top: .5rem; }
        .hasil-cari .akun-item {
            display: flex; align-items: center; gap: .6rem;
            padding: .55rem .7rem; border-radius: 10px;
            background: #fff; border: 1px solid rgba(15, 23, 42, .06);
            cursor: pointer; transition: background-color .2s ease, transform .2s var(--lembut);
        }
        .hasil-cari .akun-item:hover { background: #f1f5f5; transform: translateX(3px); }
        .hasil-cari .akun-item .awal {
            width: 26px; height: 26px; flex: none; border-radius: 9px;
            background: rgba(20, 184, 166, .1); color: var(--teal-700);
            display: grid; place-items: center; font-size: .72rem; font-weight: 700;
        }
        .hasil-cari .akun-item .info { flex: 1; min-width: 0; }
        .hasil-cari .akun-item .info .nama { font-size: .82rem; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .hasil-cari .akun-item .info .email { font-size: .72rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .hasil-cari .akun-item .peran {
            font-size: .62rem; font-weight: 700; padding: .18rem .5rem; border-radius: 999px; flex: none;
            white-space: nowrap;
        }

        /* --- PWA install btn --- */
        .pasang-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
            text-decoration: none; font-size: .74rem; font-weight: 600;
            color: var(--teal-700); padding: .5rem .8rem; border-radius: 9px;
            border: 1px solid rgba(20, 184, 166, .2);
            background: rgba(20, 184, 166, .06);
            transition: color .2s ease, background-color .2s ease, transform .2s var(--lembut);
        }
        .pasang-btn:hover { color: var(--teal-800); background: rgba(20, 184, 166, .12); transform: translateY(-1px); }
        .pasang-btn svg { width: 13px; height: 13px; }

        /* ============================================================
           ANIMASI MASUK — staggered fade-up
           ============================================================ */
        @keyframes naik-masuk {
            from { opacity: 0; transform: translate3d(0, 18px, 0) scale(.985); }
            to   { opacity: 1; transform: none; }
        }
        @keyframes naik-merek {
            from { opacity: 0; transform: translate3d(0, 26px, 0); }
            to   { opacity: 1; transform: none; }
        }
        @keyframes masuk-kiri {
            from { opacity: 0; transform: translate3d(-22px, 0, 0); }
            to   { opacity: 1; transform: none; }
        }
        .merek, .kaki-merek { animation: masuk-kiri .85s var(--lembut) both; }
        .merek { animation-delay: .05s; }
        .kaki-merek { animation-delay: .8s; }
        .isi-merek > * { animation: naik-merek .9s var(--lembut) both; }
        .isi-merek > *:nth-child(1) { animation-delay: .18s; }
        .isi-merek > *:nth-child(2) { animation-delay: .28s; }
        .isi-merek > *:nth-child(3) { animation-delay: .38s; }
        .isi-merek > *:nth-child(4) { animation-delay: .48s; }
        .kartu > *:not(form), .kartu form > * {
            animation: naik-masuk .8s var(--lembut) both;
            animation-delay: calc(.12s + var(--t, 0) * .07s);
        }

        /* transisi halaman native */
        @view-transition { navigation: auto; }
        ::view-transition-old(root) { animation: vt-lama .3s cubic-bezier(.4, 0, 1, 1) both; }
        ::view-transition-new(root) { animation: vt-baru .55s var(--lembut) both; }
        @keyframes vt-lama { to { opacity: 0; transform: translate3d(0, -8px, 0); } }
        @keyframes vt-baru { from { opacity: 0; transform: translate3d(0, 10px, 0); } }

        /* RESPONSIF */
        @media (max-width: 960px) {
            .panggung { grid-template-columns: 1fr; }
            .panel-merek {
                justify-content: flex-start; gap: 1.5rem;
                padding: 1.4rem clamp(1.1rem, 5vw, 2rem) 2.2rem;
            }
            .panel-merek::after { display: none; }
            .isi-merek { margin: 1.4rem 0 0; }
            .isi-merek h1 { font-size: clamp(1.6rem, 5.4vw, 2.2rem); }
            .isi-merek p.terang { font-size: .88rem; }
            .fitur, .kaki-merek { display: none; }
            .panel-formulir { padding: clamp(1.6rem, 6vw, 2.6rem) 1.1rem 2.6rem; }
        }
        @media (min-width: 961px) and (max-height: 840px) {
            .kartu { padding: 1.5rem 1.7rem; }
            .kartu-kepala { margin-bottom: 1.15rem; }
            .kartu-kepala .lencana { width: 46px; height: 46px; border-radius: 15px; margin-bottom: .65rem; }
            .kartu-kepala .lencana svg { width: 22px; height: 22px; }
            .kartu-kepala h2 { font-size: 1.5rem; }
            .isian { margin-bottom: .85rem; }
            .ingat { margin-bottom: 1rem; }
            .kaki-kartu { margin-top: 1.1rem; }
        }
        @media (max-width: 420px) {
            .merek .nama { font-size: 1.35rem; }
            .kartu { border-radius: 22px; }
        }

        /* reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body>
    <main class="panggung">
        @yield('konten')
    </main>

    <script>
        (function () {
            var kurangi = window.matchMedia('(prefers-reduced-motion: reduce)');

            (function tabur() {
                var wadah = document.getElementById('tabur');
                if (!wadah || kurangi.matches) return;
                for (var i = 0; i < 16; i++) {
                    var butir = document.createElement('span');
                    var ukuran = 3 + Math.random() * 7;
                    butir.className = 'butir';
                    butir.style.width = ukuran + 'px';
                    butir.style.height = ukuran + 'px';
                    butir.style.left = (Math.random() * 100).toFixed(2) + '%';
                    butir.style.animationDuration = (16 + Math.random() * 20).toFixed(1) + 's';
                    butir.style.animationDelay = (-Math.random() * 30).toFixed(1) + 's';
                    butir.style.opacity = (0.25 + Math.random() * 0.5).toFixed(2);
                    wadah.appendChild(butir);
                }
            })();

            (function pantulan() {
                var kartu = document.getElementById('kartu');
                if (!kartu || kurangi.matches) return;
                var bingkai = null, jadwal = null;
                kartu.addEventListener('pointermove', function (e) {
                    if (e.pointerType === 'touch') return;
                    if (!bingkai) bingkai = kartu.getBoundingClientRect();
                    if (!jadwal) {
                        jadwal = requestAnimationFrame(function () {
                            kartu.style.setProperty('--mx', (e.clientX - bingkai.left) + 'px');
                            kartu.style.setProperty('--my', (e.clientY - bingkai.top) + 'px');
                            jadwal = null;
                        });
                    }
                });
                window.addEventListener('scroll', function () { bingkai = null; }, { passive: true });
                window.addEventListener('resize', function () { bingkai = null; });
            })();

            (function hanyutPanel() {
                var panel = document.querySelector('.panel-merek');
                if (!panel || kurangi.matches || window.matchMedia('(hover: none)').matches) return;
                var bola = panel.querySelectorAll('.bola');
                var jx = 0, jy = 0, tx = 0, ty = 0, hidup = false;
                panel.addEventListener('pointermove', function (e) {
                    var r = panel.getBoundingClientRect();
                    tx = (e.clientX - r.left) / r.width - .5;
                    ty = (e.clientY - r.top) / r.height - .5;
                    if (!hidup) { hidup = true; tick(); }
                });
                panel.addEventListener('pointerleave', function () { tx = 0; ty = 0; });
                function tick() {
                    jx += (tx - jx) * .05;
                    jy += (ty - jy) * .05;
                    bola[0].style.translate = (jx * 26) + 'px ' + (jy * 22) + 'px';
                    bola[1].style.translate = (-jx * 20) + 'px ' + (-jy * 18) + 'px';
                    if (Math.abs(tx - jx) > .0008 || Math.abs(ty - jy) > .0008) requestAnimationFrame(tick);
                }
            })();
        })();
    </script>
    @stack('script')
</body>
</html>
