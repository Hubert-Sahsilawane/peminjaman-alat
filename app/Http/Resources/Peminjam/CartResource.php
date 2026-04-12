<?php

namespace App\Http\Resources\Peminjam;

use App\Http\Resources\Admin\ToolResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tool' => new ToolResource($this->whenLoaded('tool')),
            'quantity' => $this->quantity,
            'durasi' => $this->durasi,
            'durasi_label' => $this->getDurasiLabel(),
            'harga_satuan' => $this->harga_satuan,
            'harga_satuan_formatted' => 'Rp ' . number_format($this->harga_satuan, 0, ',', '.'),
            'subtotal' => $this->subtotal,
            'subtotal_formatted' => 'Rp ' . number_format($this->subtotal, 0, ',', '.'),
            'created_at' => $this->created_at?->toDateTimeString(),
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
