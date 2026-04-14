<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // 🔴 PERUBAHAN UTAMA: Langsung ambil dari kolom database
            'role' => $this->role,
            'role_label' => $this->getRoleLabel(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getRoleLabel(): string
    {
        $role = strtolower($this->role ?? '');

        return match ($role) {
            'admin' => 'Administrator',
            'petugas' => 'Petugas Lab',
            'peminjam' => 'Peminjam',
            default => 'Peminjam', // Jika di database benar-benar kosong (NULL)
        };
    }
}
