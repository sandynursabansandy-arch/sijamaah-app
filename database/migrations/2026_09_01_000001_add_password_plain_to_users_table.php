<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'password_plain')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('password_plain', 191)->nullable()->after('password');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'password_plain')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_plain');
        });
    }
};
