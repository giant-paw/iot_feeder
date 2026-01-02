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
        Schema::create('device_settings', function (Blueprint $table) {
            $table->id();
            
            // PENTING: Ini penghubung ke tabel devices
            // Pastikan tabel 'devices' sudah dibuat duluan sebelum tabel ini jalan
            $table->foreignId('device_id')
                  ->constrained('devices')
                  ->onDelete('cascade'); 
            
            // Konfigurasi Alat
            $table->boolean('manual_trigger')->default(false); // 0 = Standby, 1 = Beri Pakan Sekarang
            $table->integer('feed_duration')->default(5);      // Lama blower nyala (detik)
            $table->integer('servo_angle_open')->default(180);  // Sudut saat membuka
            $table->integer('servo_angle_close')->default(0);   // Sudut saat menutup
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_settings');
    }
};