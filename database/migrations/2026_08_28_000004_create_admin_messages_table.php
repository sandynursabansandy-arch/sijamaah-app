<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_messages', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id', 36);
            $table->unsignedBigInteger('santri_id');
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['santri_id', 'is_read']);
            $table->index(['batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_messages');
    }
};