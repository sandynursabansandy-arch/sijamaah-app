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
        Schema::create('presensi_sholats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('waktu_sholat', ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya']);
            $table->enum('status', ['Jamaah', 'Masbuq', 'Izin', 'Sakit', 'Alfa'])->default('Jamaah');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['santri_id', 'tanggal', 'waktu_sholat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_sholats');
    }
};
