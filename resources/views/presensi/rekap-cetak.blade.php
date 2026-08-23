<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cetak Rekap Presensi — SIJAMAAH</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @page { size: A4 landscape; margin: 8mm 6mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9pt; color: #1e293b;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
            background: #fff;
        }

        /* === PAGE ENTRY ANIMATION === */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        .anim-in {
            opacity: 0;
            animation: fadeSlideUp 0.5s ease-out forwards;
        }
        .anim-in-fast {
            opacity: 0;
            animation: fadeIn 0.4s ease-out forwards;
        }
        .anim-d1 { animation-delay: 0.05s; }
        .anim-d2 { animation-delay: 0.12s; }
        .anim-d3 { animation-delay: 0.20s; }
        .anim-d4 { animation-delay: 0.30s; }
        .anim-d5 { animation-delay: 0.40s; }
        .anim-d6 { animation-delay: 0.50s; }

        @media print {
            .anim-in, .anim-in-fast { opacity: 1 !important; animation: none !important; }
        }

        /* Saat ekspor PDF: matikan animasi agar semua elemen terrekam sempurna */
        body.pdf-export .anim-in,
        body.pdf-export .anim-in-fast { opacity: 1 !important; animation: none !important; }

        /* === SCREEN TOOLBAR === */
        .screen-only {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
            padding: 12px 16px; margin-bottom: 16px;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1px solid #bbf7d0; border-radius: 12px;
            font-size: 10pt;
        }
        .screen-only a { color: #059669; text-decoration: none; font-weight: 700; }
        .screen-only a:hover { text-decoration: underline; }
        .screen-only select, .screen-only label {
            font-size: 10pt;
        }
        .screen-only select {
            padding: 6px 10px; border: 1.5px solid #d1d5db; border-radius: 8px;
            background: #fff; font-weight: 600; color: #334155;
        }
        .screen-only .btn {
            padding: 7px 18px; border: none; border-radius: 8px;
            font-weight: 700; cursor: pointer; font-size: 10pt;
            transition: all 0.15s;
        }
        .btn-print { background: #059669; color: #fff; }
        .btn-print:hover { background: #047857; }
        .btn-pdf { background: #2563eb; color: #fff; }
        .btn-pdf:hover { background: #1d4ed8; }
        #downloadStatus { font-size: 9pt; color: #666; display: none; }
        @media print { .screen-only { display: none !important; } }

        /* === DOC HEADER === */
        .doc-header {
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
            margin-bottom: 8px;
        }

        /* -- Kop: Logo kiri, judul center, spacer kanan -- */
        .kop {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 6px;
        }
        .kop .logo { width: 60px; flex-shrink: 0; }
        .kop .title-block {
            text-align: center;
            flex: 1;
        }
        .kop .title-block h1 {
            font-size: 13pt; font-weight: 800; text-transform: uppercase;
            color: #065f46; letter-spacing: 0.8px; line-height: 1.2;
        }
        .kop .title-block h2 {
            font-size: 9pt; font-weight: 600; color: #475569;
            margin-top: 1px;
        }
        .kop .spacer { width: 60px; flex-shrink: 0; } /* simetri dengan logo */

        /* -- Garis bawah judul + badge -- */
        .hdr-divider {
            border: none;
            border-top: 2.5px solid #059669;
            margin: 4px 0 6px 0;
        }
        .badge-row {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-bottom: 6px;
        }
        .periode-badge {
            display: inline-flex; align-items: center; gap: 4px;
            background: linear-gradient(135deg, #059669, #047857); color: #fff;
            border-radius: 20px; padding: 2px 14px; font-size: 8pt;
            font-weight: 700; letter-spacing: 0.4px;
        }
        .filter-badge {
            display: inline-flex; align-items: center;
            background: #fef3c7; color: #92400e;
            border: 1px solid #f59e0b; border-radius: 20px;
            padding: 2px 12px; font-size: 7.5pt; font-weight: 700;
        }

        /* === INFO BAR === */
        .info-bar {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0;
            font-size: 7pt;
            padding: 4px 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 6px;
        }
        .info-bar .info-item {
            text-align: center;
            padding: 2px 4px;
            border-right: 1px solid #e2e8f0;
        }
        .info-bar .info-item:last-child { border-right: none; }
        .info-bar .info-label { font-weight: 600; color: #64748b; font-size: 6.5pt; text-transform: uppercase; letter-spacing: 0.2px; }
        .info-bar .info-value { font-weight: 700; color: #0f172a; margin-top: 1px; }

        /* === SUMMARY CARDS === */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 5px;
            margin-bottom: 8px;
        }
        .stat-card {
            text-align: center;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px 3px 4px 3px;
            background: #fff;
            min-height: 48px;
            display: flex; flex-direction: column; justify-content: center;
        }
        .stat-card .num {
            font-size: 14pt; font-weight: 800; line-height: 1.1; display: block;
        }
        .stat-card .lbl {
            font-size: 5.5pt; text-transform: uppercase; font-weight: 700;
            letter-spacing: 0.3px; margin-top: 1px;
        }
        .stat-card.c-merah { background: #fef2f2; border-color: #fecaca; }
        .stat-card.c-merah .num { color: #991b1b; }
        .stat-card.c-merah .lbl { color: #991b1b; }
        .stat-card.c-kuning { background: #fffbeb; border-color: #fde68a; }
        .stat-card.c-kuning .num { color: #92400e; }
        .stat-card.c-kuning .lbl { color: #92400e; }
        .stat-card.c-biru { background: #eff6ff; border-color: #bfdbfe; }
        .stat-card.c-biru .num { color: #1e40af; }
        .stat-card.c-biru .lbl { color: #1e40af; }
        .stat-card.c-hijau { background: #f0fdf4; border-color: #bbf7d0; }
        .stat-card.c-hijau .num { color: #166534; }
        .stat-card.c-hijau .lbl { color: #166534; }
        .stat-card.c-abu { background: #f8fafc; border-color: #e2e8f0; }
        .stat-card.c-abu .num { color: #334155; }
        .stat-card.c-abu .lbl { color: #475569; }

        /* Filtered mode: hide non-active, expand active */
        .summary-grid.filtered .stat-card { display: none; }
        .summary-grid.filtered .stat-card.active-filter { display: flex; grid-column: span 2; }
        .summary-grid.filtered .stat-card.c-abu { display: flex; }

        /* === TABLE === */
        table {
            width: 100%; border-collapse: separate; border-spacing: 0; font-size: 7pt;
            table-layout: fixed; margin-bottom: 8px;
            page-break-inside: auto !important;
        }
        #printContent {
            width: 100%;
            overflow: visible;
        }
        table th, table td {
            border: none; padding: 2px 2px; text-align: center;
            vertical-align: middle; overflow: hidden;
            border-right: 1px solid #d1d5db; border-bottom: 1px solid #d1d5db;
        }
        table th {
            background: #f1f5f9; font-weight: 700;
            font-size: 6pt; text-transform: uppercase; letter-spacing: 0.1px;
            color: #334155; border-color: #94a3b8;
        }
        tbody tr { page-break-inside: avoid !important; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:hover { background: #f0fdf4; }

        table th.no-col, table td.no-col { width: 22px; border-left: 1px solid #d1d5db; }
        thead tr:first-child th { border-top: 1px solid #d1d5db; }
        table th.nama-col, table td.nama-col {
            text-align: left; font-weight: 600;
            word-wrap: break-word; overflow-wrap: break-word;
        }
        table td.no-col { font-weight: 700; font-size: 6.5pt; color: #64748b; }
        table td.nama-col {
            font-size: 7pt; white-space: normal;
            color: #1e293b;
        }
        table td.nama-col .jabatan { font-size: 5.5pt; color: #64748b; font-weight: 400; }
        table th.rating-col, table td.rating-col { width: 32px; font-weight: 700; }

        /* Status cell colors - vibrant but not harsh */
        .s-j { background: #dcfce7; color: #166534; font-weight: 700; }
        .s-m { background: #fef3c7; color: #92400e; font-weight: 700; }
        .s-i { background: #dbeafe; color: #1e40af; font-weight: 700; }
        .s-a { background: #fee2e2; color: #991b1b; font-weight: 700; }
        .s-n { color: #94a3b8; }

        /* Filtered status: highlight only that column */
        .filtered-col { outline: 2px solid #059669 !important; outline-offset: -2px; }

        /* === LEGEND === */
        .legend {
            display: flex; gap: 12px; font-size: 7pt; margin-bottom: 8px;
            font-weight: 600; flex-wrap: wrap; align-items: center;
        }
        .legend span { display: flex; align-items: center; gap: 3px; }
        .legend .dot {
            display: inline-block; width: 10px; height: 10px;
            border-radius: 3px; border: 1px solid rgba(0,0,0,0.15);
        }

        /* === FOOTER === */
        .footer {
            margin-top: 6px; page-break-inside: avoid;
            padding-top: 4px; border-top: 1px solid #e2e8f0;
        }
        .footer .meta {
            text-align: right; font-size: 6pt; color: #94a3b8; margin-bottom: 2px;
        }
        .footer .sign-line {
            border-top: 1.5px solid #1e293b; margin-top: 25px; padding-top: 3px;
            font-weight: 700; text-align: right; width: 160px;
            margin-left: auto; font-size: 7pt; color: #1e293b;
        }

        /* === NO DATA === */
        .no-data {
            text-align: center; padding: 40px 20px; color: #94a3b8;
            font-size: 11pt; font-weight: 600;
        }

        @media print {
            body { margin: 0; font-size: 6.5pt; }
            .screen-only { display: none !important; }
            table { font-size: 6.5pt; }
            table th { font-size: 5.5pt; }
            table td { font-size: 6.5pt; padding: 1.5px 2px; }
            .kop .title-block h1 { font-size: 12pt; }
            .kop .title-block h2 { font-size: 8.5pt; }
            .kop .logo { width: 50px; }
            .info-bar .info-item { padding: 1px 3px; }
            .info-bar .info-label { font-size: 6pt; }
            .info-bar .info-value { font-size: 6.5pt; }
            .stat-card .num { font-size: 13pt; }
            .stat-card .lbl { font-size: 5pt; }
            .stat-card { min-height: 40px; padding: 3px 2px; }
            .footer .sign-line { font-size: 7pt; }
        }
    </style>
</head>
<body>
    {{-- ============ SCREEN-ONLY TOOLBAR ============ --}}
    <div class="screen-only anim-in-fast anim-d1">
        <a href="{{ route('presensi.rekap') }}">&larr; Kembali ke Rekap</a>

        <select id="filterWaktuSelect" onchange="gantiFilter()">
            <option value="all" {{ $filterWaktu === 'all' ? 'selected' : '' }}>Semua Waktu</option>
            @foreach($semuaWaktu as $w)
                <option value="{{ $w }}" {{ $filterWaktu === $w ? 'selected' : '' }}>{{ $w }}</option>
            @endforeach
        </select>

        <select id="filterStatusSelect" onchange="gantiFilter()">
            <option value="" {{ $filterStatus === '' ? 'selected' : '' }}>Semua Status</option>
            <option value="alfa" {{ $filterStatus === 'alfa' ? 'selected' : '' }}>Hanya Alfa</option>
            <option value="masbuq" {{ $filterStatus === 'masbuq' ? 'selected' : '' }}>Hanya Masbuq</option>
            <option value="izin" {{ $filterStatus === 'izin' ? 'selected' : '' }}>Hanya Izin</option>
        </select>

        <button class="btn btn-print" onclick="window.print()">Cetak Sekarang</button>
        <button class="btn btn-pdf" id="btnDownload" onclick="downloadPDF()">Download PDF</button>
        <span id="downloadStatus">Menyiapkan file...</span>
    </div>

    <div id="printContent">

    {{-- ============ DOCUMENT HEADER ============ --}}
    <div class="doc-header">

        {{-- Kop: Logo — Judul — Spacer --}}
        <div class="kop anim-in anim-d1">
            <img src="{{ asset('images/image.png') }}" alt="Logo" class="logo">
            <div class="title-block">
                <h1>Rekap Presensi Sholat Berjamaah</h1>
                <h2>Pesantren SIJAMAAH</h2>
            </div>
            <div class="spacer"></div>
        </div>

        <hr class="hdr-divider anim-in-fast anim-d2">

        {{-- Badge Periode + Filter --}}
        <div class="badge-row anim-in anim-d2">
            <span class="periode-badge">
                @if($periode === 'mingguan') MINGGUAN
                @elseif($periode === 'bulanan') BULANAN
                @else TAHUNAN
                @endif
            </span>
            @if($filterWaktu !== 'all')
                <span class="filter-badge">{{ $filterWaktu }}</span>
            @endif
            @if($filterStatus !== '')
                <span class="filter-badge">Hanya {{ $statusLabels[$filterStatus] ?? ucfirst($filterStatus) }}</span>
            @endif
        </div>

        {{-- Info Bar --}}
        <div class="info-bar anim-in anim-d3">
            <div class="info-item">
                <div class="info-label">Periode</div>
                <div class="info-value">{{ $periodeLabel }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Rayon</div>
                <div class="info-value">{{ $kamarId === 'all' || !$kamarId ? 'Semua Rayon' : $daftarKamar->firstWhere('id', $kamarId)->nama_kamar ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Filter</div>
                <div class="info-value">{{ $filterWaktu === 'all' ? 'Semua Waktu' : $filterWaktu }}{{ $filterStatus !== '' ? ' / ' . ($statusLabels[$filterStatus] ?? '') : '' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Santri</div>
                <div class="info-value">{{ count($rekapData) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Dicetak</div>
                <div class="info-value">{{ \Carbon\Carbon::now()->tz('Asia/Jakarta')->isoFormat('D MMM YYYY, HH:mm') }} WIB</div>
            </div>
        </div>

        {{-- Summary Stat Cards --}}
        @php $isFiltered = $filterStatus !== ''; @endphp
        <div class="summary-grid {{ $isFiltered ? 'filtered' : '' }} anim-in anim-d4">
            @if(!$isFiltered || $filterStatus === 'alfa')
                <div class="stat-card c-merah {{ $isFiltered ? 'active-filter' : '' }}">
                    <span class="num">{{ $totalAlfa }}</span>
                    <span class="lbl">Alfa</span>
                </div>
            @endif
            @if(!$isFiltered || $filterStatus === 'masbuq')
                <div class="stat-card c-kuning {{ $isFiltered ? 'active-filter' : '' }}">
                    <span class="num">{{ $totalMasbuq }}</span>
                    <span class="lbl">Masbuq</span>
                </div>
            @endif
            @if(!$isFiltered || $filterStatus === 'izin')
                <div class="stat-card c-biru {{ $isFiltered ? 'active-filter' : '' }}">
                    <span class="num">{{ $totalIzin }}</span>
                    <span class="lbl">Izin</span>
                </div>
            @endif
            @if(!$isFiltered)
                <div class="stat-card c-hijau">
                    <span class="num">{{ $totalHadir }}</span>
                    <span class="lbl">Hadir</span>
                </div>
                <div class="stat-card c-abu">
                    <span class="num">{{ $persentaseUmum }}%</span>
                    <span class="lbl">% Berjamaah</span>
                </div>
                <div class="stat-card c-abu">
                    <span class="num">{{ $totalDiisiAll }}</span>
                    <span class="lbl">Terisi</span>
                </div>
            @endif
            @if($isFiltered)
                <div class="stat-card c-abu active-filter">
                    <span class="num">{{ count($rekapData) }}</span>
                    <span class="lbl">Santri</span>
                </div>
            @endif
        </div>

    </div>

    @php
        $waktuAbbr = ['Subuh' => 'S', 'Dzuhur' => 'Dz', 'Ashar' => 'A', 'Maghrib' => 'M', 'Isya' => 'I'];
    @endphp

    @if(count($rekapData) === 0)
        <div class="no-data anim-in anim-d5">Tidak ada data untuk filter yang dipilih.</div>
    @else

    {{-- ==========================================================
         MINGGUAN
         ========================================================== --}}
    @if($periode === 'mingguan')
        <table class="anim-in anim-d5">
            <thead>
                <tr>
                    <th rowspan="2" class="no-col">No</th>
                    <th rowspan="2" class="nama-col" style="text-align:left">Nama Santri</th>
                    @foreach($allDates as $date)
                        <th colspan="{{ count($waktuList) }}"
                            style="background: #9ca3af !important; color: #fff; font-size: 6pt; font-weight: 800;
                            border-color: #6b7280; border-right: 2.5px solid #6b7280 !important;">
                            {{ $date->isoFormat('ddd') }}<br>
                            <span style="font-size:5pt; font-weight:400; opacity:0.85;">{{ $date->isoFormat('D/M') }}</span>
                        </th>
                    @endforeach
                    <th rowspan="2" class="rating-col">{{ $isFiltered ? ($statusLabels[$filterStatus] ?? '%') : '%' }}</th>
                </tr>
                <tr>
                    @foreach($allDates as $date)
                        @foreach($waktuList as $idx => $w)
                            @php
                                $subBg = match($w) {
                                    'Subuh' => '#dcfce7', 'Maghrib' => '#fef3c7', 'Isya' => '#dbeafe',
                                    default => '#fee2e2',
                                };
                                $subColor = match($w) {
                                    'Subuh' => '#166534', 'Maghrib' => '#92400e', 'Isya' => '#1e40af',
                                    default => '#991b1b',
                                };
                            @endphp
                            <th style="font-size:5.5pt; background:{{ $subBg }} !important; color:{{ $subColor }} !important; font-weight:700;
                                {{ $idx === count($waktuList) - 1 ? 'border-right: 2.5px solid #6b7280 !important;' : '' }}">
                                {{ $waktuAbbr[$w] }}
                            </th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rekapData as $i => $row)
                    <tr>
                        <td class="no-col">{{ $i + 1 }}</td>
                        <td class="nama-col">
                            {{ $row['nama'] }}
                            @if($row['jabatan']) <span class="jabatan">({{ $row['jabatan'] }})</span> @endif
                        </td>
                        @foreach($row['hariDetail'] as $hariIdx => $hari)
                            @foreach($waktuList as $wIdx => $w)
                                @php $st = $hari['statuses'][$w]; @endphp
                                <td class="{{ match($st) {
                                        'Jamaah' => 's-j', 'Masbuq' => 's-m',
                                        'Izin' => 's-i', 'Alfa' => 's-a',
                                        default => 's-n'
                                    } }}
                                    {{ $isFiltered && $st === ($statusLabels[$filterStatus] ?? '') ? 'filtered-col' : '' }}"
                                    @if($wIdx === count($waktuList) - 1) style="border-right: 2.5px solid #6b7280 !important;" @endif>
                                    @if($st === 'Jamaah') &#10003;
                                    @elseif($st === 'Masbuq') M
                                    @elseif($st === 'Izin') I
                                    @elseif($st === 'Alfa') A
                                    @else &ndash; @endif
                                </td>
                            @endforeach
                        @endforeach
                        @if(!$isFiltered || $filterStatus === '')
                            <td class="rating-col">{{ $row['persentase'] }}%</td>
                        @else
                            <td class="rating-col" style="font-weight:800; color:#059669;">
                                {{ $row[$filterStatus] ?? '' }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- ==========================================================
         BULANAN
         ========================================================== --}}
    @elseif($periode === 'bulanan')
        @php
            $weeksInMonth = [];
            for ($w = 0; $w < 4; $w++) {
                $start = $tanggalMulai->copy()->addDays($w * 7);
                $end = $w < 3
                    ? $tanggalMulai->copy()->addDays(($w + 1) * 7 - 1)
                    : $tanggalSelesai->copy();
                if ($start->lte($tanggalSelesai)) {
                    $weeksInMonth[] = ['num' => $w + 1, 'start' => $start, 'end' => $end];
                }
            }
        @endphp
        <table class="anim-in anim-d5">
            <thead>
                <tr>
                    <th rowspan="2" class="no-col">No</th>
                    <th rowspan="2" class="nama-col" style="text-align:left">Nama Santri</th>
                    @foreach($weeksInMonth as $wkIdx => $wk)
                        <th colspan="{{ $isFiltered ? 1 : 4 }}"
                            style="background: #9ca3af !important; color: #fff; font-size: 6pt; font-weight: 800;
                            border-color: #6b7280; border-right: 2.5px solid #6b7280 !important;">
                            M{{ $wk['num'] }}<br>
                            <span style="font-size:5pt; font-weight:400; opacity:0.85;">{{ $wk['start']->isoFormat('D') }}–{{ $wk['end']->isoFormat('D/M') }}</span>
                        </th>
                    @endforeach
                    <th rowspan="2" class="rating-col">{{ $isFiltered ? ($statusLabels[$filterStatus] ?? '%') : '%' }}</th>
                </tr>
                @if(!$isFiltered)
                <tr>
                    @foreach($weeksInMonth as $wkIdx => $wk)
                        <th style="font-size:5.5pt; background:#dcfce7 !important; color:#166534; font-weight:700;">H</th>
                        <th style="font-size:5.5pt; background:#fef3c7 !important; color:#92400e; font-weight:700;">Mb</th>
                        <th style="font-size:5.5pt; background:#dbeafe !important; color:#1e40af; font-weight:700;">I</th>
                        <th style="font-size:5.5pt; background:#fee2e2 !important; color:#991b1b; font-weight:700;
                            border-right: 2.5px solid #6b7280 !important;">A</th>
                    @endforeach
                </tr>
                @endif
            </thead>
            <tbody>
                @foreach($rekapData as $i => $row)
                    <tr>
                        <td class="no-col">{{ $i + 1 }}</td>
                        <td class="nama-col">
                            {{ $row['nama'] }}
                            @if($row['jabatan']) <span class="jabatan">({{ $row['jabatan'] }})</span> @endif
                        </td>
                        @foreach($weeksInMonth as $wkIdx => $wk)
                            @php
                                $wkH = 0; $wkM = 0; $wkI = 0; $wkA = 0;
                                foreach ($row['hariDetail'] as $hari) {
                                    $d = \Carbon\Carbon::parse($hari['date']);
                                    if ($d->gte($wk['start']) && $d->lte($wk['end'])) {
                                        foreach ($hari['statuses'] as $s) {
                                            match ($s) {
                                                'Jamaah' => $wkH++,
                                                'Masbuq' => $wkM++,
                                                'Izin'   => $wkI++,
                                                'Alfa'   => $wkA++,
                                                default  => null,
                                            };
                                        }
                                    }
                                }
                            @endphp
                            @if($isFiltered)
                                @php $wkVal = match($filterStatus) { 'alfa' => $wkA, 'masbuq' => $wkM, 'izin' => $wkI, default => 0 }; @endphp
                                <td class="s-{{ substr($filterStatus, 0, 1) }} {{ $wkVal > 0 ? 'filtered-col' : '' }}"
                                    style="font-size:7pt; font-weight:800;
                                    border-right: 2.5px solid #6b7280 !important;">
                                    {{ $wkVal }}
                                </td>
                            @else
                                <td class="s-j">{{ $wkH }}</td>
                                <td class="s-m">{{ $wkM }}</td>
                                <td class="s-i">{{ $wkI }}</td>
                                <td class="s-a" style="border-right: 2.5px solid #6b7280 !important;">{{ $wkA }}</td>
                            @endif
                        @endforeach
                        @if(!$isFiltered || $filterStatus === '')
                            <td class="rating-col">{{ $row['persentase'] }}%</td>
                        @else
                            <td class="rating-col" style="font-weight:800; color:#059669;">{{ $row[$filterStatus] ?? '' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- ==========================================================
         TAHUNAN
         ========================================================== --}}
    @else
        @php
            $monthsInYear = [];
            $tempM = $tanggalMulai->copy();
            while ($tempM->lte($tanggalSelesai)) {
                $monthsInYear[] = $tempM->copy();
                $tempM->addMonth();
            }
        @endphp
        <table class="anim-in anim-d5">
            <thead>
                <tr>
                    <th rowspan="2" class="no-col">No</th>
                    <th rowspan="2" class="nama-col" style="text-align:left">Nama Santri</th>
                    @foreach($monthsInYear as $mIdx => $m)
                        <th colspan="{{ $isFiltered ? 1 : 4 }}"
                            style="background: #9ca3af !important; color: #fff; font-size: 6pt; font-weight: 800;
                            border-color: #6b7280; border-right: 2.5px solid #6b7280 !important;">
                            {{ strtoupper($m->translatedFormat('M')) }}
                        </th>
                    @endforeach
                    <th rowspan="2" class="rating-col">{{ $isFiltered ? ($statusLabels[$filterStatus] ?? '%') : '%' }}</th>
                </tr>
                @if(!$isFiltered)
                <tr>
                    @foreach($monthsInYear as $mIdx => $m)
                        <th style="font-size:5.5pt; background:#dcfce7 !important; color:#166534; font-weight:700;">H</th>
                        <th style="font-size:5.5pt; background:#fef3c7 !important; color:#92400e; font-weight:700;">Mb</th>
                        <th style="font-size:5.5pt; background:#dbeafe !important; color:#1e40af; font-weight:700;">I</th>
                        <th style="font-size:5.5pt; background:#fee2e2 !important; color:#991b1b; font-weight:700;
                            border-right: 2.5px solid #6b7280 !important;">A</th>
                    @endforeach
                </tr>
                @endif
            </thead>
            <tbody>
                @foreach($rekapData as $i => $row)
                    <tr>
                        <td class="no-col">{{ $i + 1 }}</td>
                        <td class="nama-col">
                            {{ $row['nama'] }}
                            @if($row['jabatan']) <span class="jabatan">({{ $row['jabatan'] }})</span> @endif
                        </td>
                        @foreach($monthsInYear as $mIdx => $m)
                            @php
                                $mH = 0; $mMb = 0; $mI = 0; $mA = 0;
                                foreach ($row['hariDetail'] as $hari) {
                                    $d = \Carbon\Carbon::parse($hari['date']);
                                    if ($d->month === $m->month && $d->year === $m->year) {
                                        foreach ($hari['statuses'] as $s) {
                                            match ($s) {
                                                'Jamaah' => $mH++,
                                                'Masbuq' => $mMb++,
                                                'Izin'   => $mI++,
                                                'Alfa'   => $mA++,
                                                default  => null,
                                            };
                                        }
                                    }
                                }
                            @endphp
                            @if($isFiltered)
                                @php $mVal = match($filterStatus) { 'alfa' => $mA, 'masbuq' => $mMb, 'izin' => $mI, default => 0 }; @endphp
                                <td class="s-{{ substr($filterStatus, 0, 1) }} {{ $mVal > 0 ? 'filtered-col' : '' }}"
                                    style="font-size:7pt; font-weight:800;
                                    border-right: 2.5px solid #6b7280 !important;">
                                    {{ $mVal }}
                                </td>
                            @else
                                <td class="s-j">{{ $mH }}</td>
                                <td class="s-m">{{ $mMb }}</td>
                                <td class="s-i">{{ $mI }}</td>
                                <td class="s-a" style="border-right: 2.5px solid #6b7280 !important;">{{ $mA }}</td>
                            @endif
                        @endforeach
                        @if(!$isFiltered || $filterStatus === '')
                            <td class="rating-col">{{ $row['persentase'] }}%</td>
                        @else
                            <td class="rating-col" style="font-weight:800; color:#059669;">{{ $row[$filterStatus] ?? '' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- LEGEND --}}
    <div class="legend anim-in anim-d6">
        @if(!$isFiltered)
            <span><span class="dot" style="background:#dcfce7;"></span> Berjamaah</span>
            <span><span class="dot" style="background:#fef3c7;"></span> Masbuq (M)</span>
            <span><span class="dot" style="background:#dbeafe;"></span> Izin (I)</span>
            <span><span class="dot" style="background:#fee2e2;"></span> Alfa (A)</span>
        @else
            <span><span class="dot" style="background:{{ match($filterStatus) { 'alfa' => '#fee2e2', 'masbuq' => '#fef3c7', 'izin' => '#dbeafe', default => '#f1f5f9' } }};"></span>
                {{ $statusLabels[$filterStatus] ?? '' }}</span>
        @endif
        <span style="color:#94a3b8; margin-left:4px;">&ndash; = Belum Diisi</span>
    </div>

    @endif {{-- end count > 0 --}}

    {{-- FOOTER --}}
    <div class="footer anim-in anim-d6">
        <div class="meta">Dicetak otomatis dari SIJAMAAH &mdash; {{ \Carbon\Carbon::now()->tz('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }} WIB</div>
        <div class="sign-line">Sandy Nursa'ban</div>
    </div>
    </div>

    <script>
        var currentPeriode = '{{ $periode }}';
        var currentTanggal = '{{ $tanggalMulai->format("Y-m-d") }}';
        var currentKamarId = '{{ $kamarId }}';

        function gantiFilter() {
            var fw = document.getElementById('filterWaktuSelect').value;
            var fs = document.getElementById('filterStatusSelect').value;
            window.location.href = '{{ route("presensi.rekap.cetak") }}?periode=' + currentPeriode
                + '&tanggal=' + currentTanggal + '&kamar_id=' + currentKamarId
                + '&filter_waktu=' + fw + '&filter_status=' + fs;
        }

        function downloadPDF() {
            var btn = document.getElementById('btnDownload');
            var statusEl = document.getElementById('downloadStatus');
            btn.disabled = true;
            btn.style.opacity = '0.5';
            statusEl.style.display = 'inline';

            var element = document.getElementById('printContent');
            var fs = '{{ $filterStatus }}';
            var statusSuffix = fs ? '_' + fs.charAt(0).toUpperCase() + fs.slice(1) : '';
            var filename = 'Rekap_' + '{{ $periodeLabel }}'.replace(/[^\w]/g, '_')
                + '{{ $filterWaktu !== "all" ? "_" . $filterWaktu : "" }}'
                + statusSuffix + '.pdf';

            // Matikan animasi entrance agar seluruh elemen terrekam dengan opacity penuh
            document.body.classList.add('pdf-export');

            // Tunggu semua gambar (logo) selesai dimuat sebelum merekam
            var imgs = Array.prototype.slice.call(element.querySelectorAll('img'));
            Promise.all(imgs.map(function(img) {
                return img.complete ? Promise.resolve() : new Promise(function(res) { img.onload = img.onerror = res; });
            })).then(function() {
                var opt = {
                    margin:      [8, 6, 8, 6],
                    filename:    filename,
                    image:       { type: 'jpeg', quality: 0.95 },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        letterRendering: true,
                        scrollY: 0,
                        windowWidth: element.scrollWidth || document.documentElement.scrollWidth,
                        backgroundColor: '#ffffff'
                    },
                    jsPDF:       { unit: 'mm', format: 'a4', orientation: 'landscape' },
                    pagebreak:   { mode: ['css', 'legacy'], avoid: ['tr', '.doc-header', '.footer'] }
                };

                return html2pdf().set(opt).from(element).save();
            }).then(function() {
                document.body.classList.remove('pdf-export');
                btn.disabled = false;
                btn.style.opacity = '1';
                statusEl.style.display = 'none';
            }).catch(function() {
                document.body.classList.remove('pdf-export');
                btn.disabled = false;
                btn.style.opacity = '1';
                statusEl.style.display = 'none';
                alert('Gagal membuat PDF. Gunakan Cetak Sekarang sebagai alternatif.');
            });
        }
    </script>
</body>
</html>
