<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Absensi Mustahik & Munawib — SANTRI.ID</title>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
*{box-sizing:border-box}
body{margin:0;background:#eee;font-family:Arial,sans-serif;color:#111;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.page{width:1400px;margin:25px auto;background:#fff;padding:28px 30px 35px;border:2px solid #222}
h1{text-align:center;font-size:25px;margin:0 0 14px}
.info{font-weight:bold;font-size:15px;line-height:1.5;margin-bottom:12px}
.table-wrap{overflow-x:auto}
table{border-collapse:collapse;width:100%;table-layout:fixed}
th,td{border:1px solid #222;text-align:center;padding:4px;font-size:12px;height:25px}
thead th{background:#fff900;font-weight:bold}
.name{width:190px;text-align:left;padding-left:8px}
.no{width:42px}
.date{width:34px}
.status-H{background:#c8f7c5}.status-M{background:#ffe58a}
.status-I{background:#bfe3ff}.status-S{background:#ddd}
.status-A{background:#ffb3b3}
.section-title{text-align:center;font-size:21px;font-weight:bold;margin:18px 0 8px;page-break-after:avoid;break-after:avoid}
.recap-grid{display:grid;grid-template-columns:1fr 1fr;gap:25px;page-break-inside:avoid;break-inside:avoid}
.recap th,.recap td{height:24px;font-size:12px}
.recap-no{width:28px}
.recap-legend{display:flex;justify-content:flex-start;align-items:center;gap:20px;flex-wrap:wrap;margin-top:22px;font-size:12px;font-weight:bold;text-align:left;page-break-inside:avoid;break-inside:avoid}
.recap-legend span{white-space:nowrap}
.recap-legend .legend-label{font-weight:bold}
.legend-code{display:inline-block;min-width:20px;margin-right:4px;padding:1px 4px;border:1px solid #222;border-radius:3px}
.legend-code.h{background:#c8f7c5}
.legend-code.m{background:#ffe58a}
.legend-code.i{background:#bfe3ff}
.legend-code.a{background:#ffb3b3}
.controls{display:flex;gap:8px;align-items:center;margin:10px 0;flex-wrap:wrap}
.save-status{font-size:12px;color:#555}
select,button{padding:7px 10px;border:1px solid #777;background:white}
button{cursor:pointer;background:#fff900;font-weight:bold}
@media print{
 body{background:#fff}
 .page{width:100%;margin:0;border:2px solid #222;padding:20px}
  .controls,.save-status{display:none}
 @page{size:A4 landscape;margin:5mm}
 th,td{font-size:8px;height:15px;padding:2px}
  .name{width:115px}.no{width:25px}.date{width:22px}
 .recap-no{width:20px}
 .recap-page{page-break-inside:auto}
 .recap-page .section-title{margin-top:0}
 .recap-page .recap th,.recap-page .recap td{height:15px;font-size:8px}
  .recap-legend{margin-top:12px;padding-top:0;font-size:10px}
  .status-H{background-color:#c8f7c5 !important}
  .status-M{background-color:#ffe58a !important}
  .status-I{background-color:#bfe3ff !important}
  .status-S{background-color:#ddd !important}
  .status-A{background-color:#ffb3b3 !important}
  thead th{background-color:#fff900 !important}
  .legend-code.h{background-color:#c8f7c5 !important}
  .legend-code.m{background-color:#ffe58a !important}
  .legend-code.i{background-color:#bfe3ff !important}
  .legend-code.a{background-color:#ffb3b3 !important}
  .section-title{font-size:15px}
  h1{font-size:18px}
 .info{font-size:10px}
}
body.pdf-export .page{width:1400px;margin:0 auto;padding:18px 22px;border:1px solid #222}
body.pdf-export h1{font-size:17px;margin:0 0 6px}
body.pdf-export .info{font-size:10px;line-height:1.35;margin-bottom:6px}
body.pdf-export th,body.pdf-export td{font-size:8px;height:14px;padding:2px}
body.pdf-export .name{width:115px}
body.pdf-export .no{width:25px}
body.pdf-export .date{width:21px}
body.pdf-export .recap-no{width:20px}
body.pdf-export .section-title{font-size:14px;margin:8px 0 4px}
body.pdf-export .recap-grid{gap:14px}
body.pdf-export .recap th,body.pdf-export .recap td{height:14px;font-size:8px}
body.pdf-export .recap-legend{margin-top:8px;font-size:10px}
body.pdf-export .anim-in,body.pdf-export .anim-in-fast{opacity:1 !important;animation:none !important}
/* Saat ekspor: jangan biarkan wrapper scroll memotong kolom tabel yg lebar */
body.pdf-export .table-wrap{overflow:visible !important}
</style>
</head>
<body>
<div class="page">
  <div class="controls">
    <a href="{{ route('presensi.rekapBerjamaah') }}" style="padding:7px 10px;border:1px solid #777;background:white;text-decoration:none;color:#111;">&larr; Kembali</a>
    <button type="button" id="savePdfButton" data-period="{{ $periodeLabel }}" onclick="saveAttendancePdf()">Simpan PDF</button>
    <button type="button" onclick="window.print()">Cetak</button>
  </div>
  <div id="savePdfStatus" class="save-status"></div>

  <h1>ABSENSI MUSTAHIK &amp; MUNAWIB</h1>

  <div class="info">
    @php
        $kegiatanLabel = $filterWaktu === 'all'
            ? 'BERJAMAAH SEMUA WAKTU'
            : 'BERJAMAAH ' . strtoupper(implode(' + ', explode(',', $filterWaktu)));
    @endphp
    BULAN : {{ strtoupper($periodeLabel) }}<br>
    KEGIATAN : {{ $kegiatanLabel }}
  </div>

  @php
      $abbr     = ['Jamaah' => 'H', 'Masbuq' => 'M', 'Izin' => 'I', 'Alfa' => 'A'];
      $priority = ['Alfa' => 4, 'Izin' => 3, 'Masbuq' => 2, 'Jamaah' => 1];
      $daysInMonth = (int) $tanggalMulai->format('t');

      $rows = [];
      foreach ($rekapData as $row) {
          $perDay = [];
          foreach ($row['hariDetail'] as $hari) {
              $day = (int) date('j', strtotime($hari['date']));
              $best = null;
              $bestScore = 0;
              foreach ($waktuList as $w) {
                  $st = $hari['statuses'][$w] ?? '-';
                  $score = $priority[$st] ?? 0;
                  if ($score > $bestScore) {
                      $bestScore = $score;
                      $best = $st;
                  }
              }
              $perDay[$day] = $best ? ($abbr[$best] ?? '') : '';
          }

          $H = $M = $I = $A = 0;
          foreach ($perDay as $L) {
              match ($L) {
                  'H' => $H++,
                  'M' => $M++,
                  'I' => $I++,
                  'A' => $A++,
                  default => null,
              };
          }

          $rows[] = [
              'nama' => $row['nama'],
              'days' => $perDay,
              'H' => $H, 'M' => $M, 'I' => $I, 'A' => $A,
          ];
      }
  @endphp

  <div class="table-wrap">
    <table id="attendance">
      <thead>
        <tr>
          <th rowspan="2" class="no">NO</th>
          <th rowspan="2" class="name">NAMA</th>
          <th colspan="{{ $daysInMonth }}">TANGGAL</th>
        </tr>
        <tr>
          @for($d = 1; $d <= $daysInMonth; $d++)
            <th class="date">{{ $d }}</th>
          @endfor
        </tr>
      </thead>
      <tbody>
        @foreach($rows as $i => $r)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td class="name">{{ $r['nama'] }}</td>
          @for($d = 1; $d <= $daysInMonth; $d++)
            @php $ls = $r['days'][$d] ?? ''; @endphp
            <td class="status-{{ $ls }}"><span class="abs">{{ $ls }}</span></td>
          @endfor
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="recap-page">
    <div class="section-title">RINCIAN REKAPITULASI KEHADIRAN</div>
    <div class="recap-grid">
      @php $mid = (int) ceil(count($rows) / 2); @endphp
      @foreach([
          ['data' => array_slice($rows, 0, $mid), 'start' => 0],
          ['data' => array_slice($rows, $mid), 'start' => $mid],
      ] as $block)
        <table class="recap">
          <thead>
            <tr><th class="recap-no">NO</th><th>NAMA</th><th>HADIR</th><th>MASBUQ</th><th>IZIN</th><th>ALFA</th><th>%</th></tr>
          </thead>
          <tbody>
            @foreach($block['data'] as $j => $r)
              @php
                  $no = $block['start'] + $j + 1;
                  $filled = $r['H'] + $r['M'] + $r['I'] + $r['A'];
                  $pct = $filled > 0 ? round(($r['H'] + $r['M']) / $filled * 100) : 0;
              @endphp
              <tr>
                <td class="recap-no">{{ $no }}</td>
                <td class="name">{{ $r['nama'] }}</td>
                <td>{{ $r['H'] }}</td>
                <td>{{ $r['M'] }}</td>
                <td>{{ $r['I'] }}</td>
                <td>{{ $r['A'] }}</td>
                <td>{{ $pct }}%</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endforeach
    </div>
    <div class="recap-legend">
      <span class="legend-label">Keterangan:</span>
      <span><span class="legend-code h">H</span> = Hadir</span>
      <span><span class="legend-code m">M</span> = Masbuq</span>
      <span><span class="legend-code i">I</span> = Izin</span>
      <span><span class="legend-code a">A</span> = Alfa</span>
    </div>
  </div>
</div>
<script>
function saveAttendancePdf() {
    var btn = document.getElementById('savePdfButton');
    var status = document.getElementById('savePdfStatus');

    if (typeof html2canvas === 'undefined' || !(window.jspdf && window.jspdf.jsPDF)) {
        status.textContent = 'Pustaka PDF belum siap dimuat. Tunggu sebentar lalu klik lagi.';
        return;
    }

    btn.disabled = true;
    btn.style.opacity = '0.5';
    status.textContent = 'Menyiapkan file PDF, mohon tunggu...';

    var element = document.querySelector('.page');
    var controls = element.querySelector('.controls');
    var prevControlsDisplay = controls ? controls.style.display : '';

    // Sembunyikan kontrol & matikan animasi agar seluruh elemen terrekam penuh.
    if (controls) controls.style.display = 'none';
    document.body.classList.add('pdf-export');
    var prevScrollY = window.scrollY;
    window.scrollTo(0, 0);

    var finish = function () {
        document.body.classList.remove('pdf-export');
        if (controls) controls.style.display = prevControlsDisplay || '';
        window.scrollTo(0, prevScrollY);
        btn.disabled = false;
        btn.style.opacity = '1';
    };

    // Skala tinggi agar teks tetap tajam saat diperkecil ke lebar A4.
    // Diturunkan otomatis bila halaman sangat panjang agar tidak kehabisan memori.
    var SCALE = 2;
    if (element.scrollHeight > 5000) SCALE = 1.5;
    if (element.scrollHeight > 9000) SCALE = 1.2;

    // Tunggu logo/gambar selesai dimuat sebelum merekam.
    var imgs = Array.prototype.slice.call(element.querySelectorAll('img'));
    var waitImgs = Promise.all(imgs.map(function (img) {
        return img.complete ? Promise.resolve() : new Promise(function (res) { img.onload = img.onerror = res; });
    }));

    waitImgs.then(function () {
        return html2canvas(element, {
            scale: SCALE,
            useCORS: true,
            allowTaint: false,
            backgroundColor: '#ffffff',
            logging: false,
            scrollX: 0,
            scrollY: 0,
            windowWidth: element.scrollWidth,
            windowHeight: element.scrollHeight
        });
    }).then(function (canvas) {
        var jsPDF = window.jspdf.jsPDF;
        var pdf = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'landscape' });

        var marginMm = 5;
        var innerWidthMm = 297 - marginMm * 2;   // A4 landscape lebar 297mm
        var innerHeightMm = 210 - marginMm * 2;
        var mmPerPx = innerWidthMm / canvas.width; // seluruh lebar konten dipetakan ke lebar halaman
        var pageHeightPx = innerHeightMm / mmPerPx;

        // Titik potong halaman diambil dari batas bawah tiap baris tabel / blok
        // agar tidak ada baris yang terbelah di antara dua halaman.
        var elRect = element.getBoundingClientRect();
        var breaks = [0, canvas.height];
        var nodes = element.querySelectorAll('tr, .section-title, .recap-legend, h1, .info');
        Array.prototype.forEach.call(nodes, function (n) {
            var r = n.getBoundingClientRect();
            var bottom = Math.round((r.bottom - elRect.top) * SCALE);
            if (bottom > 0 && bottom < canvas.height) breaks.push(bottom);
        });
        breaks.sort(function (a, b) { return a - b; });

        var cuts = [0];
        var start = 0;
        while (start < canvas.height - 1) {
            var ideal = start + pageHeightPx;
            if (ideal >= canvas.height) { cuts.push(canvas.height); break; }
            var chosen = -1;
            for (var i = 0; i < breaks.length; i++) {
                if (breaks[i] > start + 1 && breaks[i] <= ideal) chosen = breaks[i];
            }
            if (chosen < 0) chosen = Math.round(ideal);
            cuts.push(chosen);
            start = chosen;
        }

        for (var p = 0; p < cuts.length - 1; p++) {
            var y0 = cuts[p];
            var hPx = cuts[p + 1] - y0;
            if (hPx <= 0) continue;

            var slice = document.createElement('canvas');
            slice.width = canvas.width;
            slice.height = hPx;
            var ctx = slice.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, slice.width, slice.height);
            ctx.drawImage(canvas, 0, y0, canvas.width, hPx, 0, 0, canvas.width, hPx);

            var imgData = slice.toDataURL('image/jpeg', 0.92);
            if (p > 0) pdf.addPage();
            pdf.addImage(imgData, 'JPEG', marginMm, marginMm, innerWidthMm, hPx * mmPerPx);
        }

        var period = (btn.getAttribute('data-period') || '').replace(/[^\w]+/g, '_');
        pdf.save('Absensi_' + period + '.pdf');
    }).then(function () {
        status.textContent = '';
        finish();
    }).catch(function (e) {
        console.error(e);
        status.textContent = 'Gagal membuat PDF. Coba lagi atau gunakan tombol "Cetak".';
        finish();
    });
}
</script>
</body>
</html>
