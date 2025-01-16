<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'sensor_data';

    // Kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'humidity',
        'temperature',
        'smoke',
        'created_at',
    ];

    // Menentukan apakah timestamps digunakan
    public $timestamps = false;

    /**
     * Format data yang ditampilkan
     * Misalnya, ubah timestamp menjadi lebih mudah dibaca.
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
