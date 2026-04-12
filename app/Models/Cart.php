<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'harga_satuan' => 'integer',
        'subtotal' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    // Ambil harga berdasarkan durasi
    public static function getHargaByDurasi($tool, $durasi)
    {
        return match ($durasi) {
            '6jam' => $tool->harga_6jam,
            '12jam' => $tool->harga_12jam,
            '24jam' => $tool->harga_24jam,
            default => $tool->harga_24jam,
        };
    }
}
