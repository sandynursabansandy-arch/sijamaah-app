<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('title', 191);
            $table->string('url', 191);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};