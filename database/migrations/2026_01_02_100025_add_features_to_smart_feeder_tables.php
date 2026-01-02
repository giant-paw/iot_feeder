<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // 1. Tambah kolom 'emergency_stop' di tabel settings
    Schema::table('device_settings', function (Blueprint $table) {
        $table->boolean('emergency_stop')->default(false)->after('manual_trigger');
    });

    // 2. Tambah kolom 'days' di tabel schedules (Disimpan sebagai teks: "Mon,Tue,Wed")
    Schema::table('schedules', function (Blueprint $table) {
        $table->string('days')->default('All')->after('feed_time'); // "All" atau "Mon,Wed,Fri"
    });
}

public function down(): void
{
    Schema::table('device_settings', function (Blueprint $table) {
        $table->dropColumn('emergency_stop');
    });
    Schema::table('schedules', function (Blueprint $table) {
        $table->dropColumn('days');
    });
}
};
