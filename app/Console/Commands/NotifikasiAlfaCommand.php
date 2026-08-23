<?php

namespace App\Console\Commands;

use App\Services\PresensiAnalysis;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifikasiAlfaCommand extends Command
{
    protected $signature = 'presensi:notifikasi-alfa {--min=3 : Minimal hari alfa beruntun}';
    protected $description = 'Kirim notifikasi email ke admin tentang santri yang alfa beruntun';

    public function handle(): int
    {
        $min = (int) $this->option('min');
        $santriAlfa = PresensiAnalysis::alfaBeruntun($min);

        if (empty($santriAlfa)) {
            $this->info('Tidak ada santri dengan alfa >= ' . $min . ' hari beruntun.');
            return self::SUCCESS;
        }

        $lines = ["Berikut santri dengan alfa {$min} hari atau lebih beruntun:", ''];
        foreach ($santriAlfa as $s) {
            $lines[] = "- {$s['nama']} ({$s['kamar']}) — {$s['streak']} hari beruntun";
        }
        $body = implode("\n", $lines);

        $recipients = User::whereIn('role', ['admin', 'musyrif'])->pluck('email')->all();

        if (empty($recipients)) {
            $this->warn('Tidak ada user admin/musyrif untuk dikirimi email.');
            return self::SUCCESS;
        }

        $terkirim = 0;
        foreach ($recipients as $email) {
            try {
                Mail::raw($body, function ($message) use ($email) {
                    $message->to($email)
                        ->subject('[SIJAMAAH] Peringatan Alfa Beruntun')
                        ->from(config('mail.from.address'), config('mail.from.name'));
                });
                $terkirim++;
            } catch (\Throwable $e) {
                $this->warn("Gagal kirim ke {$email}: " . $e->getMessage());
            }
        }

        $this->info("Notifikasi terkirim ke {$terkirim} penerima. " . count($santriAlfa) . ' santri terdeteksi.');
        return self::SUCCESS;
    }
}
