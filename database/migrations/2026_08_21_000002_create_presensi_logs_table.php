<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('waktu_sholat', 20);
            $table->string('status_lama', 20)->nullable();
            $table->string('status_baru', 20)->nullable();
            $table->string('aksi', 30)->default('Ubah');
            $table->timestamps();

            $table->index(['tanggal', 'waktu_sholat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_logs');
    }
};
