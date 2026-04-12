<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_alat' => $this->nama_alat,
            'deskripsi' => $this->deskripsi,
            'stok' => $this->stok,
            'harga_6jam' => $this->harga_6jam,
            'harga_6jam_formatted' => 'Rp ' . number_format($this->harga_6jam, 0, ',', '.'),
            'harga_12jam' => $this->harga_12jam,
            'harga_12jam_formatted' => 'Rp ' . number_format($this->harga_12jam, 0, ',', '.'),
            'harga_24jam' => $this->harga_24jam,
            'harga_24jam_formatted' => 'Rp ' . number_format($this->harga_24jam, 0, ',', '.'),
            'gambar_url' => $this->gambar ? asset('storage/' . $this->gambar) : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
