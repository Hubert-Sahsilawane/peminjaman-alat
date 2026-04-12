<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $guarded = [];

    protected $casts = [
        'harga_6jam' => 'integer',
        'harga_12jam' => 'integer',
        'harga_24jam' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Format harga ke Rupiah
    public function getHarga6jamFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_6jam, 0, ',', '.');
    }

    public function getHarga12jamFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_12jam, 0, ',', '.');
    }

    public function getHarga24jamFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_24jam, 0, ',', '.');
    }
}
