<?php

namespace App\Services\Auth;

use App\Models\User;
//use App\Models\ActivityLog;/
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function attemptLogin(array $credentials): array|null
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        // Hapus token lama (opsional)
        $user->tokens()->delete();

        // Buat token baru untuk Passport
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;  // <-- Untuk Passport

        // Catat activity log (comment dulu jika error)
        // ActivityLog::create([
        //     'user_id' => $user->id,
        //     'action' => 'Login',
        //     'description' => 'Pengguna ' . $user->email . ' melakukan login'
        // ]);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
