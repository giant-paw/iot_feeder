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

        $device->update(['status' => 'ONLINE', 'last_seen' => now()]);
        $settings = $device->setting;
        
        // Filter Hari
        $today = now()->format('D'); 
        
        // AMBIL JADWAL (HANYA JAM:MENIT)
        $schedules = $device->schedules()
            ->where('is_active', true)
            ->get()
            ->filter(function ($schedule) use ($today) {
                $days = is_array($schedule->days) ? $schedule->days : explode(',', $schedule->days);
                return in_array('All', $days) || in_array($today, $days);
            })
            ->map(function ($schedule) {
                // PENTING: Format jadi H:i (Contoh: "07:00") - HILANGKAN DETIK
                return \Carbon\Carbon::parse($schedule->feed_time)->format('H:i');
            })
            ->values();

        return response()->json([
            // Kirim Jam Server tanpa detik juga
            'server_time' => now()->format('H:i'), 
            'force_feed'  => (int) $settings->manual_trigger,
            'emergency'   => (int) $settings->emergency_stop,
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