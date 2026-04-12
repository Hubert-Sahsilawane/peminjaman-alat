<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function attemptRegister(array $data): array|null
    {
        try {
            // Buat user baru dengan role default 'peminjam'
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'peminjam',  // Default role
            ]);

            // Assign role peminjam menggunakan Spatie
            $user->assignRole('peminjam');

            // Buat token untuk Passport
            $tokenResult = $user->createToken('auth_token');
            $token = $tokenResult->accessToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
