<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            if (!Schema::hasColumn('santris', 'no_hp_wali')) {
                $table->string('no_hp_wali', 20)->nullable();
            }

            if (!Schema::hasColumn('santris', 'status')) {
                $table->enum('status', ['Aktif', 'Izin', 'Sakit', 'Nonaktif'])->default('Aktif');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            if (Schema::hasColumn('santris', 'no_hp_wali')) {
                $table->dropColumn('no_hp_wali');
            }

            if (Schema::hasColumn('santris', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
