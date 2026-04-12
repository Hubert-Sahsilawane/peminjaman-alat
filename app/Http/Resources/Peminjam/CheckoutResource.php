<?php

namespace App\Http\Resources\Peminjam;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Checkout berhasil! Menunggu konfirmasi petugas.',
            'data' => [
                'invoice_number' => $this['invoice_number'],
                'items' => CheckoutItemResource::collection($this['items']),
                'grand_total' => $this['grand_total'],
                'grand_total_formatted' => 'Rp ' . number_format($this['grand_total'], 0, ',', '.'),
                'total_items' => $this['items']->count(),
                'tanggal_pinjam' => $this['tanggal_pinjam'],
                'tanggal_kembali' => $this['tanggal_kembali'],
                'status' => 'pending',
                'status_label' => 'Menunggu Persetujuan',
            ]
        ];
    }
}
