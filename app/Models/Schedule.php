<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Schedule extends Model
{
    protected $guarded = ['id'];

    // Otomatis ubah "Mon,Tue" jadi Array ["Mon", "Tue"] saat diambil
    // Dan ubah Array jadi String saat disimpan
    protected function days(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === 'All' ? ['All'] : explode(',', $value),
            set: fn ($value) => is_array($value) ? implode(',', $value) : $value,
        );
    }
}