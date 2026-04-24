<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proxy_hosts', function (Blueprint $table) {
            $table->json('hostnames')->nullable()->after('name');
        });

        // Bestehende Daten migrieren
        $hosts = DB::table('proxy_hosts')->get();
        foreach ($hosts as $host) {
            $hname = property_exists($host, 'hostname') ? $host->hostname : null;
            if ($hname) {
                DB::table('proxy_hosts')
                    ->where('id', $host->id)
                    ->update(['hostnames' => json_encode([$hname])]);
            } else {
                DB::table('proxy_hosts')
                    ->where('id', $host->id)
                    ->update(['hostnames' => json_encode([])]);
            }
        }

        Schema::table('proxy_hosts', function (Blueprint $table) {
            // Index explizit löschen für SQLite Kompatibilität
            $table->dropIndex(['hostname']);
            $table->dropColumn('hostname');
        });
    }

    public function down(): void
    {
        Schema::table('proxy_hosts', function (Blueprint $table) {
            $table->string('hostname')->nullable()->after('name');
            $table->index('hostname');
        });
        Schema::table('proxy_hosts', function (Blueprint $table) {
            $table->dropColumn('hostnames');
        });
    }
};
