<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdminPesan
{
    public static function ensureTable(): void
    {
        // SchemaCache: hasTable() di-cache antar request, jadi polling badge
        // tidak memicu query information_schema tiap 30 detik.
        if (!SchemaCache::hasTable('admin_messages')) {
            try {
                Schema::create('admin_messages', function (Blueprint $table) {
                    $table->id();
                    $table->string('batch_id', 36);
                    $table->unsignedBigInteger('santri_id');
                    $table->unsignedBigInteger('sent_by')->nullable();
                    $table->text('pesan');
                    $table->boolean('is_read')->default(false);
                    $table->timestamp('read_at')->nullable();
                    $table->timestamp('user_hidden_at')->nullable();
                    $table->timestamps();
                    $table->index(['santri_id', 'is_read']);
                    $table->index(['batch_id']);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                if (!Schema::hasTable('admin_messages')) {
                    throw $e;
                }
            }

            SchemaCache::flush();
        }

        self::ensureUserHiddenAt();
    }

    public static function ensureUserHiddenAt(): void
    {
        if (SchemaCache::hasColumn('admin_messages', 'user_hidden_at')) {
            return;
        }

        try {
            Schema::table('admin_messages', function (Blueprint $table) {
                $table->timestamp('user_hidden_at')->nullable()->after('read_at');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (SchemaCache::hasColumn('admin_messages', 'user_hidden_at')) {
                return;
            }
            throw $e;
        }

        SchemaCache::flush();
    }
}
