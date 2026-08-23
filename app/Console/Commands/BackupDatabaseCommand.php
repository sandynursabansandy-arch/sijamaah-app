<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup {--keep=7 : Jumlah backup yang disimpan}';
    protected $description = 'Backup database MySQL ke storage/app/backups';

    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            $this->error("Backup otomatis hanya didukung untuk koneksi mysql (aktif: {$connection}).");
            return self::FAILURE;
        }

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileName = 'backup_' . $database . '_' . date('Y-m-d_His') . '.sql';
        $target = $dir . DIRECTORY_SEPARATOR . $fileName;

        $mysqldump = $this->findMysqldump();
        if ($mysqldump === null) {
            $this->error('mysqldump tidak ditemukan. Pastikan MySQL/Laragon terpasang.');
            return self::FAILURE;
        }

        $cmd = sprintf(
            '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > "%s"',
            $mysqldump,
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg((string) $password),
            escapeshellarg($database),
            $target
        );

        exec($cmd . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($target)) {
            $this->error('Backup gagal: ' . implode("\n", $output));
            return self::FAILURE;
        }

        $size = round(filesize($target) / 1024, 1);
        $this->info("Backup berhasil: {$fileName} ({$size} KB)");

        // Hapus backup lama, simpan hanya --keep terbaru
        $files = glob($dir . '/backup_*.sql');
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        foreach (array_slice($files, max(1, (int) $this->option('keep'))) as $old) {
            @unlink($old);
        }

        return self::SUCCESS;
    }

    protected function findMysqldump(): ?string
    {
        $candidates = [
            'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe',
            'C:\laragon\bin\mysql\latest\bin\mysqldump.exe',
        ];

        foreach (glob('C:\laragon\bin\mysql\*\bin\mysqldump.exe') ?: [] as $path) {
            $candidates[] = $path;
        }
        $candidates[] = 'mysqldump';

        foreach ($candidates as $candidate) {
            if ($candidate === 'mysqldump') {
                exec('where mysqldump 2>nul', $out, $code);
                if ($code === 0) return 'mysqldump';
                continue;
            }
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
