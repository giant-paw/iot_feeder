<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            // Kapasitas dalam satuan detik (Total detik galon penuh sampai habis)
            // Misal galon Le Minerale habis dalam total 600 detik curahan
            $table->integer('max_capacity')->default(600); 
            $table->integer('current_capacity')->default(600); 
        });
    }

    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn(['max_capacity', 'current_capacity']);
        });
    }
};
