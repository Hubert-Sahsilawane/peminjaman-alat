<?php

namespace App\Http\Resources\Peminjam;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_alat' => $this->tool->nama_alat,
            'quantity' => $this->stok,
            'durasi' => $this->durasi,
            'durasi_label' => $this->getDurasiLabel(),
            'harga_satuan' => $this->harga_satuan,
            'harga_satuan_formatted' => 'Rp ' . number_format($this->harga_satuan, 0, ',', '.'),
            'subtotal' => $this->subtotal,
            'subtotal_formatted' => 'Rp ' . number_format($this->subtotal, 0, ',', '.'),
        ];
    }

    private function getDurasiLabel(): string
    {
        return match ($this->durasi) {
            '6jam' => '6 Jam',
            '12jam' => '12 Jam',
            '24jam' => '24 Jam',
            default => '24 Jam',
        };
    }
}
