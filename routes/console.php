<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Notifikasi alfa beruntun setiap malam
Schedule::command('presensi:notifikasi-alfa --min=3')->dailyAt('20:30');

// Backup database otomatis setiap malam
Schedule::command('db:backup --keep=7')->dailyAt('23:00');
