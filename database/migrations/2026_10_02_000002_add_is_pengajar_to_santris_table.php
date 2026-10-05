<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            if (!Schema::hasColumn('santris', 'is_pengajar')) {
                $table->boolean('is_pengajar')->default(false)->after('jabatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            if (Schema::hasColumn('santris', 'is_pengajar')) {
                $table->dropColumn('is_pengajar');
            }
        });
    }
};
