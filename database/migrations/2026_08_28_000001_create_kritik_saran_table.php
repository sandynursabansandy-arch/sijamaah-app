<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kritik_saran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kritik_saran');
    }
};