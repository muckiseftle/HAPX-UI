<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_metrics', function (Blueprint $table) {
            $table->float('cpu_usage')->default(0)->after('avg_response_ms');
            $table->float('ram_usage')->default(0)->after('cpu_usage');
            $table->float('disk_usage')->default(0)->after('ram_usage');
        });
    }

    public function down(): void
    {
        Schema::table('performance_metrics', function (Blueprint $table) {
            $table->dropColumn(['cpu_usage', 'ram_usage', 'disk_usage']);
        });
    }
};
