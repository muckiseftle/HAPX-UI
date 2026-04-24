<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proxy_hosts', function (Blueprint $col) {
            $col->id();
            $col->string('name');
            $col->string('hostname')->nullable()->index();
            $col->enum('mode', ['http', 'tcp'])->default('http');
            $col->boolean('is_active')->default(true);
            $col->string('listen_address')->default('*');
            $col->integer('listen_port')->default(80);
            $col->boolean('force_https')->default(false);
            $col->boolean('tls_termination')->default(false);
            $col->string('certificate_path')->nullable();
            $col->string('balance_algorithm')->default('roundrobin');
            $col->text('description')->nullable();
            $col->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proxy_hosts');
    }
};
