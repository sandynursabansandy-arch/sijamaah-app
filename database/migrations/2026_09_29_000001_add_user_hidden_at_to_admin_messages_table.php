<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_messages')) {
            return;
        }

        if (Schema::hasColumn('admin_messages', 'user_hidden_at')) {
            return;
        }

        Schema::table('admin_messages', function (Blueprint $table) {
            $table->timestamp('user_hidden_at')->nullable()->after('read_at');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admin_messages')) {
            return;
        }

        if (!Schema::hasColumn('admin_messages', 'user_hidden_at')) {
            return;
        }

        Schema::table('admin_messages', function (Blueprint $table) {
            $table->dropColumn('user_hidden_at');
        });
    }
};
