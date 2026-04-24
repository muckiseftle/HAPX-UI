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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->enum('type', ['self-signed', 'letsencrypt'])->default('self-signed');
            $table->enum('status', ['active', 'pending', 'expired', 'failed'])->default('pending');
            $table->string('dns_provider')->nullable();
            $table->json('dns_credentials')->nullable();
            $table->string('path')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_renewed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
