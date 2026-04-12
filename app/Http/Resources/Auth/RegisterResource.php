<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => [
                    'id' => $this['user']->id,
                    'name' => $this['user']->name,
                    'email' => $this['user']->email,
                    'role' => $this['user']->role,
                ],
            ],
        ];
    }
}
