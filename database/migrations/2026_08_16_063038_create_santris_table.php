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
    Schema::create('santris', function (Blueprint $table) {
        $table->id();
        $table->string('nis', 20)->unique();
        $table->string('nama', 100);
        $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
        $table->string('kelas', 50);
        $table->string('no_hp_wali', 20)->nullable();
        $table->enum('status', ['Aktif', 'Izin', 'Sakit', 'Nonaktif'])->default('Aktif');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santris');
    }
};
