<?php

namespace App\Providers;

use App\Support\SchemaCache;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cache metadata skema (SchemaCache) di-invalidate otomatis setiap kali
        // migration jalan, supaya request berikutnya tidak memakai informasi
        // kolom/tabel yang sudah usang.
        Event::listen(MigrationsEnded::class, function () {
            SchemaCache::flush();
        });
    }
}
