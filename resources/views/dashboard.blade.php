<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Feeder Ultimate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen text-gray-800 pb-10">

    <div class="max-w-md mx-auto min-h-screen bg-gray-50 shadow-2xl relative overflow-hidden">
        
        <div class="bg-blue-700 p-8 rounded-b-[40px] shadow-lg text-white relative z-10">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold tracking-wide">Smart Feeder</h1>
                    <p class="text-blue-200 text-xs">Galon Le Minerale Edition</p>
                </div>
                <div class="flex items-center gap-2 bg-white/10 px-3 py-1 rounded-full border border-white/20">
                    <div class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $device->status == 'ONLINE' ? 'bg-green-400' : 'bg-red-400' }} opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 {{ $device->status == 'ONLINE' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </div>
                    <span class="text-xs font-semibold">{{ $device->status }}</span>
                </div>
            </div>
            <div class="mt-4 text-center">
                <span class="text-[10px] uppercase tracking-widest opacity-70">Last Seen</span>
                <p class="font-mono text-sm font-bold">{{ $device->last_seen ? \Carbon\Carbon::parse($device->last_seen)->diffForHumans() : '-' }}</p>
            </div>
        </div>

        <div class="px-6 -mt-8 relative z-20 space-y-5">

            <form action="{{ route('device.emergency', $device->id) }}" method="POST">
                @csrf
                @if($device->setting->emergency_stop)
                    <button type="submit" class="w-full bg-red-600 text-white p-4 rounded-2xl shadow-xl shadow-red-500/30 animate-pulse flex items-center justify-center gap-4 border-2 border-red-400">
                        <i class="fas fa-exclamation-triangle text-3xl"></i>
                        <div class="text-left">
                            <h3 class="font-bold text-lg leading-none">DARURAT AKTIF</h3>
                            <p class="text-[10px] opacity-90">Sistem Dibekukan. Klik untuk Normalkan.</p>
                        </div>
                    </button>
                @else
                    <button type="submit" class="w-full bg-white/90 backdrop-blur text-red-500 py-2 rounded-xl shadow-sm border border-red-100 flex items-center justify-center gap-2 hover:bg-red-50 transition">
                        <i class="fas fa-power-off"></i>
                        <span class="font-bold text-xs">Emergency Stop</span>
                    </button>
                @endif
            </form>

            <div class="bg-white rounded-2xl p-4 shadow-md border border-gray-100">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase">Sisa Pakan Galon</span>
                    <span class="text-xs font-bold text-blue-600">
                        @php
                            $persen = 0;
                            if($device->setting->max_capacity > 0) {
                                $persen = round(($device->setting->current_capacity / $device->setting->max_capacity) * 100);
                            }
                        @endphp
                        {{ $persen }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2.5 rounded-full transition-all duration-1000" 
                         style="width: {{ $persen }}%"></div>
                </div>
                <form action="{{ route('device.refill', $device->id) }}" method="POST" class="text-right">
                    @csrf
                    <button type="submit" class="text-[10px] text-blue-500 hover:text-blue-700 underline font-bold cursor-pointer">
                        <i class="fas fa-sync-alt"></i> Isi Ulang Penuh
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-md border border-gray-100 text-center relative overflow-hidden">
                @if($device->setting->emergency_stop)
                    <div class="absolute inset-0 bg-gray-100/90 z-10 flex items-center justify-center text-gray-400 text-xs font-bold">
                        <div class="text-center">
                            <i class="fas fa-lock text-2xl mb-2"></i><br>SISTEM TERKUNCI
                        </div>
                    </div>
                @endif

                <h2 class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-4">Manual Control</h2>
                
                @if($device->setting->manual_trigger)
                    <div class="animate-pulse mb-4">
                        <div class="text-blue-500 text-sm font-bold mb-1">PROSES BERJALAN...</div>
                        <div class="text-xs text-gray-400">Pakan sedang keluar</div>
                    </div>
                    <form action="{{ route('device.stop_feed', $device->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-32 h-32 rounded-full bg-red-50 border-4 border-red-500 text-red-600 shadow-xl flex flex-col items-center justify-center mx-auto hover:bg-red-100 transition transform hover:scale-105">
                            <i class="fas fa-square text-3xl mb-1"></i>
                            <span class="font-bold text-sm">STOP</span>
                        </button>
                    </form>
                    <p class="text-[10px] text-red-400 mt-3 font-semibold">Klik untuk berhenti paksa</p>

                @else
                    <form action="{{ route('device.feed', $device->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="group relative w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 shadow-xl shadow-blue-500/40 flex flex-col items-center justify-center mx-auto transition hover:scale-105 active:scale-95">
                            <i class="fas fa-utensils text-3xl text-white mb-1"></i>
                            <span class="text-white font-bold text-sm">FEED</span>
                        </button>
                    </form>
                    <p class="text-[10px] text-gray-400 mt-3">Tekan untuk pakan instan</p>
                @endif

                @if(session('success'))
                    <div class="mt-4 text-green-600 text-xs font-bold bg-green-50 py-2 rounded-lg border border-green-100">
                        <i class="fas fa-check"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mt-4 text-red-600 text-xs font-bold bg-red-50 py-2 rounded-lg border border-red-100">
                        <i class="fas fa-times"></i> {{ session('error') }}
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-md border border-gray-100">
                <h3 class="font-bold text-gray-700 text-sm mb-4 flex items-center gap-2">
                    <i class="far fa-calendar-alt text-blue-500"></i> Jadwal Pakan
                </h3>

                <ul class="space-y-3 mb-6">
                    @forelse($device->schedules as $schedule)
                    <li class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <div>
                            <span class="font-mono text-xl font-bold text-gray-700 block leading-none">
                                {{ \Carbon\Carbon::parse($schedule->feed_time)->format('H:i') }}
                            </span>
                            <span class="text-[9px] uppercase font-bold text-blue-500 tracking-wide mt-1 block">
                                {{ is_array($schedule->days) ? implode(', ', $schedule->days) : str_replace(',', ', ', $schedule->days) }}
                            </span>
                        </div>
                        <form action="{{ route('schedule.delete', $schedule->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="w-8 h-8 rounded-full bg-white text-red-400 hover:text-red-600 shadow-sm flex items-center justify-center hover:shadow transition">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </li>
                    @empty
                    <li class="text-center text-xs text-gray-400 py-4 border border-dashed rounded-xl">Belum ada jadwal aktif.</li>
                    @endforelse
                </ul>

                <form action="{{ route('schedule.add', $device->id) }}" method="POST" class="border-t pt-4">
                    @csrf
                    <div class="flex justify-between mb-3 px-1">
                        @foreach(['Mon'=>'S','Tue'=>'S','Wed'=>'R','Thu'=>'K','Fri'=>'J','Sat'=>'S','Sun'=>'M'] as $day => $label)
                        <label class="cursor-pointer group relative">
                            <input type="checkbox" name="days[]" value="{{ $day }}" class="peer sr-only">
                            <div class="w-7 h-7 rounded-full bg-gray-100 text-gray-400 peer-checked:bg-blue-600 peer-checked:text-white flex items-center justify-center text-[10px] font-bold transition-all border border-transparent peer-checked:border-blue-400">
                                {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    
                    <div class="flex gap-2">
                        <input type="time" name="feed_time" required class="flex-1 bg-gray-100 rounded-xl px-3 text-sm font-bold text-gray-600 outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-black transition">
                            TAMBAH
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-md border border-gray-100">
                <h3 class="font-bold text-gray-700 text-sm mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-orange-500"></i> Konfigurasi
                </h3>
                <form action="{{ route('device.update', $device->id) }}" method="POST" class="grid grid-cols-2 gap-3">
                    @csrf
                    <div>
                        <label class="text-[9px] uppercase font-bold text-gray-400 block mb-1">Durasi (Detik)</label>
                        <input type="number" name="duration" value="{{ $device->setting->feed_duration }}" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-center font-bold text-sm focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="text-[9px] uppercase font-bold text-gray-400 block mb-1">Sudut Servo</label>
                        <input type="number" name="angle" value="{{ $device->setting->servo_angle_open }}" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-center font-bold text-sm focus:ring-blue-500 outline-none">
                    </div>
                    <button class="col-span-2 mt-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2 rounded-lg transition">
                        SIMPAN PERUBAHAN
                    </button>
                </form>
            </div>

            <div class="text-center pb-8">
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-2">Riwayat Eksekusi</p>
                @foreach($logs as $log)
                    <div class="inline-block bg-white border border-gray-200 rounded-full px-3 py-1 mb-1 shadow-sm mx-1">
                        <span class="text-[10px] text-gray-500 font-mono">{{ \Carbon\Carbon::parse($log->executed_at)->format('d/m H:i') }}</span>
                        <span class="text-[10px] font-bold ml-1 
                            {{ $log->trigger == 'MANUAL' ? 'text-blue-500' : 
                              ($log->trigger == 'STOPPED' ? 'text-red-500' : 'text-green-500') }}">
                            {{ $log->trigger }}
                        </span>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</body>
</html>