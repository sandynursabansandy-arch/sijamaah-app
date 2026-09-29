<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Absensi Mustahik & Munawib — SIJAMAAH</title>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
 @page{size:A4 landscape;margin:8mm}
 th,td{font-size:8px;height:18px;padding:2px}
  .name{width:115px}.no{width:25px}.date{width:22px}
  .recap-no{width:20px}
  .recap-page{page-break-before:always;break-before:page;min-height:172mm}
  .recap-page .section-title{margin-top:0}
  .recap-page .recap th,.recap-page .recap td{height:18px;font-size:8px}
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
    var button = document.getElementById('savePdfButton');
    var status = document.getElementById('savePdfStatus');
    var element = document.querySelector('.page');
    var controls = document.querySelector('.controls');

    if (typeof html2pdf !== 'function') {
        status.textContent = 'Simpan PDF tidak tersedia. Gunakan Cetak lalu pilih Save as PDF.';
        return;
    }

    var originalControlsDisplay = controls.style.display;
    var originalStatusDisplay = status.style.display;
    button.disabled = true;
    status.textContent = 'Menyiapkan PDF...';
    controls.style.display = 'none';
    status.style.display = 'none';

    var filename = ('Absensi_Mustahik_Munawib_' + button.dataset.period)
        .replace(/[^a-z0-9_-]+/gi, '_') + '.pdf';
    var options = {
        margin: [8, 6, 8, 6],
        filename: filename,
        image: {type: 'jpeg', quality: 0.95},
        html2canvas: {
            scale: 2,
            useCORS: true,
            letterRendering: true,
            scrollY: 0,
            windowWidth: element.scrollWidth || document.documentElement.scrollWidth,
            backgroundColor: '#ffffff'
        },
        jsPDF: {unit: 'mm', format: 'a4', orientation: 'landscape'},
        pagebreak: {
            mode: ['css', 'legacy'],
            before: function(node) {
                return node && node.classList && node.classList.contains('recap-page');
            },
            avoid: ['tr', '.recap-grid', '.recap-legend']
        }
    };

    html2pdf().set(options).from(element).save()
        .then(function() {
            controls.style.display = originalControlsDisplay;
            status.style.display = originalStatusDisplay;
            button.disabled = false;
            status.textContent = 'PDF berhasil disimpan.';
        })
        .catch(function() {
            controls.style.display = originalControlsDisplay;
            status.style.display = originalStatusDisplay;
            button.disabled = false;
            status.textContent = 'Gagal membuat PDF. Gunakan Cetak lalu pilih Save as PDF.';
        });
}
</script>
</body>
</html>