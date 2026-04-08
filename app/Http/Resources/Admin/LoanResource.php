<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'tool' => new ToolResource($this->whenLoaded('tool')),
            'petugas' => new UserResource($this->whenLoaded('petugas')),
            'tanggal_pinjam' => $this->tanggal_pinjam,
            'tanggal_kembali' => $this->tanggal_kembali,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'is_late' => $this->isLate(),
            'days_late' => $this->getDaysLate(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'disetujui' => 'Sedang Dipinjam',
            'ditolak' => 'Ditolak',
            'kembali' => 'Sudah Dikembalikan',
            default => 'Unknown',
        };
    }

    private function isLate(): bool
    {
        if ($this->status === 'disetujui') {
            return now()->format('Y-m-d') > $this->tanggal_kembali;
        }
        return false;
    }

    private function getDaysLate(): ?int
    {
        if ($this->status === 'disetujui' && $this->isLate()) {
            $today = now()->format('Y-m-d');
            return now()->parse($today)->diffInDays(now()->parse($this->tanggal_kembali));
        }
        return null;
    }
}
