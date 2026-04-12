<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;

class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'user' => new UserResource($this->whenLoaded('user')),
            'tool' => new ToolResource($this->whenLoaded('tool')),
            'petugas' => new UserResource($this->whenLoaded('petugas')),

            // ✅ Gunakan 'stok' (bukan 'items_count')
            'stok' => $this->stok,
            'items_count' => $this->stok,  // untuk kompatibilitas frontend
            'quantity' => $this->stok,

            'durasi' => $this->durasi,
            'durasi_label' => $this->getDurasiLabel(),
            'harga_satuan' => $this->harga_satuan,
            'harga_satuan_formatted' => 'Rp ' . number_format($this->harga_satuan, 0, ',', '.'),
            'subtotal' => $this->subtotal,
            'subtotal_formatted' => 'Rp ' . number_format($this->subtotal, 0, ',', '.'),
            'denda' => $this->denda,
            'denda_formatted' => 'Rp ' . number_format($this->denda, 0, ',', '.'),
            'total_bayar' => $this->total_bayar,
            'total_bayar_formatted' => 'Rp ' . number_format($this->total_bayar, 0, ',', '.'),
            'tanggal_pinjam' => $this->tanggal_pinjam,
            'tanggal_kembali' => $this->tanggal_kembali,
            'tanggal_kembali_aktual' => $this->tanggal_kembali_aktual,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_pembayaran' => $this->status_pembayaran,
            'status_pembayaran_label' => $this->getStatusPembayaranLabel(),
            'is_late' => $this->isLate(),
            'days_late' => $this->getDaysLate(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
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

    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'disetujui' => 'Sedang Dipinjam',
            'ditolak' => 'Ditolak',
            'kembali' => 'Sudah Dikembalikan',
            'telat' => 'Telat Pengembalian',
            default => 'Unknown',
        };
    }

    private function getStatusPembayaranLabel(): string
    {
        return match ($this->status_pembayaran) {
            'pending' => 'Belum Dibayar',
            'lunas' => 'Lunas',
            default => 'Unknown',
        };
    }

    private function isLate(): bool
    {
        if (in_array($this->status, ['disetujui', 'telat'])) {
            return now()->format('Y-m-d') > $this->tanggal_kembali;
        }
        if ($this->status === 'kembali' && $this->tanggal_kembali_aktual) {
            return $this->tanggal_kembali_aktual > $this->tanggal_kembali;
        }
        return false;
    }

    private function getDaysLate(): ?int
    {
        if ($this->status === 'kembali' && $this->tanggal_kembali_aktual) {
            $days = $this->tanggal_kembali_aktual->diffInDays($this->tanggal_kembali, false);
            return $days > 0 ? $days : null;
        }
        if ($this->isLate()) {
            $days = now()->diffInDays($this->tanggal_kembali, false);
            return $days > 0 ? $days : null;
        }
        return null;
    }
}
