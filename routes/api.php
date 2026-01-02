<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IotController;

// Route untuk ESP32 sinkronisasi data (GET)
Route::get('/device/sync', [IotController::class, 'sync']);

// Route untuk ESP32 lapor setelah pakan keluar (POST)
Route::post('/device/log', [IotController::class, 'log']);