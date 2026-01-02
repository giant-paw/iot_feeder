<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Jalur komunikasi untuk Halaman Website (Dashboard Admin/User).
|
*/

// 1. Halaman Utama (Dashboard)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// 2. Aksi Tombol Manual (Feed Now)
Route::post('/feed/{id}', [DashboardController::class, 'triggerFeed'])->name('device.feed');

// 3. Aksi Tombol Emergency Stop (PENTING: Ini yang tadi kurang)
Route::post('/emergency/{id}', [DashboardController::class, 'emergencyStop'])->name('device.emergency');

// 4. Update Settingan Alat (Durasi & Sudut)
Route::post('/settings/{id}', [DashboardController::class, 'updateSettings'])->name('device.update');

// 5. Tambah Jadwal Baru
Route::post('/schedule/{id}', [DashboardController::class, 'addSchedule'])->name('schedule.add');

// 6. Hapus Jadwal
Route::delete('/schedule/{id}', [DashboardController::class, 'deleteSchedule'])->name('schedule.delete');

Route::post('/feed/stop/{id}', [DashboardController::class, 'stopFeed'])->name('device.stop_feed');

Route::post('/feed/refill/{id}', [DashboardController::class, 'refillFood'])->name('device.refill');