<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proxy_backends', function (Blueprint $col) {
            $col->id();
            $col->foreignId('proxy_host_id')->constrained()->onDelete('cascade');
            $col->string('name');
            $col->string('address');
            $col->integer('port');
            $col->boolean('is_backup')->default(false);
            $col->boolean('is_active')->default(true);
            $col->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proxy_backends');
    }
};
