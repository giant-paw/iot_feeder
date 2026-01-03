<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            // Default 30 gram/detik (Sesuai hitungan kasar kita tadi)
            $table->integer('flow_rate')->default(30); 
        });
    }

    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn('flow_rate');
        });
    }
};
