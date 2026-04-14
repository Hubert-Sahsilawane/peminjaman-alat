<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali' => 'date',
        'stok' => 'integer',
        'harga_satuan' => 'integer',
        'subtotal' => 'integer',
        'denda' => 'integer',
        'total_bayar' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    // Generate invoice number
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && $lastInvoice->invoice_number) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'INV/' . $year . $month . '/' . $newNumber;
    }

    // Hitung denda
    public function calculateDenda()
    {
        if ($this->tanggal_kembali_aktual && $this->tanggal_kembali_aktual > $this->tanggal_kembali) {
            $lateDays = $this->tanggal_kembali_aktual->diffInDays($this->tanggal_kembali);
            return $lateDays * 5000; // Rp 5.000 per hari
        }
        return 0;
    }
}
