<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\User\UserResource;  // ✅ PERBAIKI: import yang benar
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'Profil user',
            'data' => new UserResource($user)
        ], 200);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $data = [
            'name' => $request->name,
        ];

        if ($request->email && $request->email !== $user->email) {
            $data['email'] = $request->email;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => new UserResource($user->fresh())
        ], 200);
    }
}
