<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $guarded = ['id']; // Biar bisa insert data langsung banyak

    public function setting() {
        return $this->hasOne(DeviceSetting::class);
    }
    public function schedules() {
        return $this->hasMany(Schedule::class);
    }
}