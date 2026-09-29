<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminNotif
{
    public static function ensureTable(): void
    {
        // SchemaCache: hasTable() di-cache antar request, jadi polling badge
        // tidak memicu query information_schema tiap 30 detik.
        if (SchemaCache::hasTable('admin_notifications')) {
            return;
        }

        try {
            Schema::create('admin_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('type', 20);
                $table->string('title', 191);
                $table->string('url', 191);
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                $table->index(['is_read', 'created_at']);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (Schema::hasTable('admin_notifications')) {
                return;
            }
            throw $e;
        }
    }

    public static function push(string $type, string $title, string $url): void
    {
        self::ensureTable();

        DB::table('admin_notifications')->insert([
            'type' => $type,
            'title' => $title,
            'url' => $url,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function markTypeRead(string $type): void
    {
        self::ensureTable();

        DB::table('admin_notifications')
            ->where('type', $type)
            ->where('is_read', false)
            ->update(['is_read' => true, 'updated_at' => now()]);
    }
}