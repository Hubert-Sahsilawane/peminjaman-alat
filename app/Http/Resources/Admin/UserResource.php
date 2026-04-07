<?php

namespace App\Http\Resources\Admin;

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
            'role' => $this->getRoleNames()->first(),
            'role_label' => $this->getRoleLabel(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getRoleLabel(): string
    {
        $role = $this->getRoleNames()->first();
        return match ($role) {
            'admin' => 'Administrator',
            'petugas' => 'Petugas Lab',
            'peminjam' => 'Peminjam',
            default => 'Unknown',
        };
    }
}
