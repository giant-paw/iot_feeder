<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Schedule;
use App\Models\FeedLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil Data Alat
        $device = Device::with(['setting', 'schedules' => function($q) {
            $q->orderBy('feed_time', 'asc'); 
        }])->first();

        // Auto-Create jika kosong
        if (!$device) {
            $device = Device::create([
                'name' => 'Smart Feeder',
                'device_token' => '123456',
                'status' => 'OFFLINE'
            ]);
            $device->setting()->create(); 
        }

        // 2. Ambil Riwayat
        $logs = FeedLog::where('device_id', $device->id)
                       ->latest('executed_at')
                       ->take(5)
                       ->get();

        return view('dashboard', compact('device', 'logs'));
    }

    // --- FITUR 1: FEED MANUAL ---
    public function triggerFeed($id)
    {
        $device = Device::findOrFail($id);
        
        // Cek dulu apakah sedang Emergency?
        if($device->setting->emergency_stop) {
            return back()->with('error', 'Gagal! Matikan dulu Mode Darurat.');
        }

        $device->setting()->update(['manual_trigger' => true]);
        return back()->with('success', 'Perintah pakan dikirim!');
    }

    // --- FITUR 2: EMERGENCY STOP (INI YANG TADI HILANG) ---
    public function emergencyStop($id)
    {
        $device = Device::findOrFail($id);
        
        // Ambil status sekarang, lalu balik (True jadi False, False jadi True)
        $currentStatus = $device->setting->emergency_stop;
        $newStatus = !$currentStatus;

        // Update database
        // Jika Emergency ON, kita juga matikan manual_trigger biar aman
        $device->setting()->update([
            'emergency_stop' => $newStatus,
            'manual_trigger' => 0 
        ]);

        $pesan = $newStatus ? '⛔ SISTEM DIMATIKAN DARURAT!' : '✅ Sistem Kembali Normal.';
        
        // Kirim notifikasi sesuai status
        if ($newStatus) {
            return back()->with('error', $pesan); // Merah
        } else {
            return back()->with('success', $pesan); // Hijau
        }
    }

    // --- FITUR 3: UPDATE SETTING ---
    public function updateSettings(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $request->validate([
            'duration' => 'required|numeric|min:1|max:60',
            'angle' => 'required|numeric|min:0|max:180',
        ]);

        $device->setting()->update([
            'feed_duration' => $request->duration,
            'servo_angle_open' => $request->angle,
        ]);

        return back()->with('success', 'Konfigurasi disimpan!');
    }

    // --- FITUR 4: TAMBAH JADWAL ---
    public function addSchedule(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $request->validate([
            'feed_time' => 'required',
            'days' => 'array' // Menerima input array dari checkbox
        ]);

        // Jika user tidak centang hari apa-apa, default ke "All" (Setiap Hari)
        // Implode mengubah array ["Mon", "Tue"] menjadi string "Mon,Tue"
        $days = $request->has('days') ? implode(',', $request->days) : 'All';

        // Cek duplikasi jam
        $exists = $device->schedules()->where('feed_time', $request->feed_time)->exists();
        
        if(!$exists) {
            $device->schedules()->create([
                'feed_time' => $request->feed_time,
                'days' => $days,
                'is_active' => true
            ]);
            return back()->with('success', 'Jadwal baru ditambahkan.');
        }

        return back()->with('error', 'Jadwal jam tersebut sudah ada.');
    }

    // --- FITUR 5: HAPUS JADWAL ---
    public function deleteSchedule($id)
    {
        Schedule::destroy($id);
        return back()->with('success', 'Jadwal dihapus.');
    }

    public function stopFeed($id)
    {
        $device = Device::findOrFail($id);
        
        // Ubah manual_trigger jadi 0 (Artinya: "Batal/Berhenti")
        // Kita TIDAK menyalakan emergency, cuma membatalkan perintah pakan
        $device->setting()->update(['manual_trigger' => false]);

        return back()->with('success', 'Perintah pakan dibatalkan!');
    }

    // Tambahkan logika Reset Kapasitas (Isi Ulang Galon)
    public function refillFood($id)
    {
        $device = Device::findOrFail($id);
        // Reset kapasitas ke penuh
        $device->setting()->update([
            'current_capacity' => $device->setting->max_capacity
        ]);
        return back()->with('success', 'Galon berhasil diisi ulang!');
    }
}