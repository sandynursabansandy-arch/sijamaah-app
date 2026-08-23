<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            if (!Schema::hasColumn('santris', 'jabatan')) {
                $table->string('jabatan', 100)->nullable()->after('kelas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            if (Schema::hasColumn('santris', 'jabatan')) {
                $table->dropColumn('jabatan');
            }
        });
    }
};
