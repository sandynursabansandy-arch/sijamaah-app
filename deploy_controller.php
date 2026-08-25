<?php
/**
 * DEPLOY CONTROLLER + DIAGNOSTIC - SIJAMAAH
 * Self-contained: upload to hosting, visit URL, then test ranking-alfa.
 */
@set_time_limit(300);
header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);

echo "=== DEPLOY CONTROLLER + DIAGNOSTIC ===\n\n";

/* ---------- Find app root ---------- */
function looksLikeAppRoot($d) {
    return @is_dir($d . '/resources') && @is_dir($d . '/vendor') && @is_file($d . '/index.php');
}
$target = null;
foreach (array_unique(array_merge([__DIR__, dirname(__DIR__)], glob(dirname(__DIR__) . '/*'))) as $c) {
    if (@looksLikeAppRoot($c)) { $target = $c; break; }
}
if ($target === null) {
    exit("ERROR: Application folder not found.\n");
}
echo "App root: $target\n";

/* ---------- Read current controller ---------- */
$controllerPath = 'app/Http/Controllers/PresensiSholatController.php';
$currentFile = $target . '/' . $controllerPath;

if (is_file($currentFile)) {
    $currentContent = file_get_contents($currentFile);
    echo "Current controller size: " . strlen($currentContent) . " bytes\n";
    
    // Check if rankingAlfa method exists
    if (strpos($currentContent, 'function rankingAlfa') !== false) {
        echo "rankingAlfa method: EXISTS\n";
    } else {
        echo "rankingAlfa method: MISSING!\n";
    }
    
    // Check if rankingAlfa has whereIn status filter
    if (preg_match('/rankingAlfa.*?whereIn.*?status/s', $currentContent)) {
        echo "whereIn status filter: FOUND\n";
    } else {
        echo "whereIn status filter: NOT FOUND in rankingAlfa\n";
    }
    
    // Check if rankingBerjamaah method exists
    if (strpos($currentContent, 'function rankingBerjamaah') !== false) {
        echo "rankingBerjamaah method: EXISTS\n";
    } else {
        echo "rankingBerjamaah method: MISSING!\n";
    }
    
    // Check PHP version info
    echo "\nPHP version: " . phpversion() . "\n";
    echo "Laravel: " . (class_exists('Illuminate\Foundation\Application') ? 'YES' : 'NO') . "\n";
    
    // Check if Schema hasColumn works
    try {
        $schema = \Illuminate\Support\Facades\Schema::getFacadeRoot();
        echo "Schema facade: OK\n";
    } catch (\Throwable $e) {
        echo "Schema facade ERROR: " . $e->getMessage() . "\n";
    }
    
    // Check if presensi_sholats table exists and has status column
    try {
        $hasTable = \Illuminate\Support\Facades\Schema::hasTable('presensi_sholats');
        echo "presensi_sholats table: " . ($hasTable ? 'EXISTS' : 'MISSING') . "\n";
        if ($hasTable) {
            $hasStatus = \Illuminate\Support\Facades\Schema::hasColumn('presensi_sholats', 'status');
            echo "presensi_sholats.status column: " . ($hasStatus ? 'EXISTS' : 'MISSING') . "\n";
            
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('presensi_sholats');
            echo "presensi_sholats columns: " . implode(', ', $columns) . "\n";
        }
    } catch (\Throwable $e) {
        echo "DB check ERROR: " . $e->getMessage() . "\n";
    }
    
    // Check Santri model
    try {
        $santriCount = \App\Models\Santri::count();
        echo "Santri count: $santriCount\n";
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('santris', 'status')) {
            $activeCount = \App\Models\Santri::where('status', 'Aktif')->count();
            echo "Active santri: $activeCount\n";
        }
    } catch (\Throwable $e) {
        echo "Santri ERROR: " . $e->getMessage() . "\n";
    }
    
    // Try running the rankingAlfa query directly
    echo "\n--- Testing rankingAlfa query ---\n";
    try {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        
        $santriQuery = \App\Models\Santri::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('santris', 'status')) {
            $santriQuery->where('status', 'Aktif');
        }
        
        $santris = $santriQuery->with(['presensis' => function ($query) use ($start_date, $end_date) {
            $query->whereIn('status', ['Alfa', 'Masbuq', 'Izin'])
                  ->whereBetween('tanggal', [$start_date, $end_date]);
        }])->get();
        
        echo "Query OK! Santri loaded: " . $santris->count() . "\n";
        echo "Total presensi loaded: " . $santris->sum(fn($s) => $s->presensis->count()) . "\n";
        
        // Test the mapping logic
        $bobot = ['Alfa' => 5, 'Masbuq' => 2, 'Izin' => 1];
        $rankingData = $santris->map(function ($santri) use ($bobot) {
            $alfaCount = 0; $masbuqCount = 0; $izinCount = 0; $poin = 0;
            foreach ($santri->presensis as $p) {
                match ($p->status) {
                    'Alfa'   => $alfaCount++,
                    'Masbuq' => $masbuqCount++,
                    'Izin'   => $izinCount++,
                    default  => null,
                };
                $poin += $bobot[$p->status] ?? 0;
            }
            return [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'jabatan' => $santri->jabatan,
                'nis' => $santri->nis,
                'kamar' => $santri->kamar?->nama_kamar ?? '-',
                'alfa_count' => $alfaCount,
                'masbuq_count' => $masbuqCount,
                'izin_count' => $izinCount,
                'poin' => $poin,
            ];
        })->sortByDesc('alfa_count')->values();
        
        echo "Mapping OK! Ranking items: " . $rankingData->count() . "\n";
        echo "\nTOP 3:\n";
        foreach ($rankingData->take(3) as $i => $item) {
            echo ($i + 1) . ". {$item['nama']} - Alfa: {$item['alfa_count']}, Poin: {$item['poin']}\n";
        }
        
        // Now try rendering the actual blade
        echo "\n--- Testing blade rendering ---\n";
        ob_start();
        $view = view('presensi.ranking-alfa', compact(
            'kamarId', 'daftarKamar', 'start_date', 'end_date', 'rankingData'
        ));
        // Need daftarKamar and kamarId
        $daftarKamar = \App\Models\Kamar::all();
        $kamarId = 'all';
        $view = view('presensi.ranking-alfa', compact(
            'kamarId', 'daftarKamar', 'start_date', 'end_date', 'rankingData'
        ));
        $rendered = $view->render();
        ob_end_clean();
        echo "Blade rendered OK! Output size: " . strlen($rendered) . " bytes\n";
        
    } catch (\Throwable $e) {
        echo "ERROR: " . get_class($e) . "\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Code: " . $e->getCode() . "\n";
    }
    
} else {
    echo "WARNING: Controller file not found at $currentFile\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "If you see errors above, they explain the 500 error.\n";
