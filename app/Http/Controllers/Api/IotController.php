<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\FeedLog;

class IotController extends Controller
{
    // 1. ESP32 MEMINTA DATA (Polling)
    public function sync(Request $request)
    {
        $token = $request->query('token');
        $device = Device::where('device_token', $token)->first();

        if (!$device) return response()->json(['status' => 'error'], 401);

        // Update status online & last seen
        $device->update(['status' => 'ONLINE', 'last_seen' => now()]);

        $settings = $device->setting;
        
        // --- LOGIKA FILTER JADWAL BERDASARKAN HARI ---
        $today = now()->format('D'); // Mon, Tue, Wed...
        
        $schedules = $device->schedules()
            ->where('is_active', true)
            ->get()
            ->filter(function ($schedule) use ($today) {
                // Ambil data hari (array atau string)
                $days = is_array($schedule->days) ? $schedule->days : explode(',', $schedule->days);
                // Loloskan jika hari ini ada di daftar atau jika jadwal "All"
                return in_array('All', $days) || in_array($today, $days);
            })
            ->pluck('feed_time') // Hanya ambil jamnya
            ->values(); // Reset array keys

        return response()->json([
            'server_time' => now()->format('H:i:s'), // Format Detik penting untuk presisi
            'force_feed'  => (int) $settings->manual_trigger, // 1 = FEED, 0 = STOP/IDLE
            'emergency'   => (int) $settings->emergency_stop, // 1 = MATI TOTAL
            'config' => [
                'duration'    => $settings->feed_duration,
                'servo_open'  => $settings->servo_angle_open,
            ],
            'schedules' => $schedules
        ]);
    }

    // 2. ESP32 LAPOR SELESAI (Logging)
    public function log(Request $request)
    {
        $token = $request->query('token');
        $device = Device::where('device_token', $token)->first();

        if (!$device) return response()->json(['status' => 'error'], 401);

        $triggerSource = $request->input('trigger', 'SCHEDULE');

        // Simpan Riwayat
        FeedLog::create([
            'device_id'   => $device->id,
            'executed_at' => now(),
            'trigger'     => $triggerSource,
            'status'      => 'SUCCESS'
        ]);

        // --- LOGIKA PENGURANGAN STOK PAKAN ---
        // Hanya kurangi stok jika pakan selesai normal (Bukan distop paksa/Emergency)
        if ($triggerSource != 'STOPPED' && $triggerSource != 'EMERGENCY_STOP') {
            $duration = $device->setting->feed_duration;
            
            // Kurangi kapasitas saat ini (decrement)
            $device->setting()->decrement('current_capacity', $duration);
            
            // Pastikan tidak minus
            if($device->setting->current_capacity < 0) {
                $device->setting()->update(['current_capacity' => 0]);
            }
        }

        // Matikan tombol manual jika proses selesai
        // (Baik sukses maupun distop, tombol harus kembali ke 0)
        if ($triggerSource == 'MANUAL' || $triggerSource == 'STOPPED' || $triggerSource == 'MANUAL_DONE') {
            $device->setting()->update(['manual_trigger' => 0]);
        }

        return response()->json(['status' => 'ok']);
    }
}