<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function getAllUsers(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function getUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function createUser(array $data): User
    {
        try {
            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);
            $user->assignRole($data['role']);

            $userId = Auth::id();
            if ($userId) {
                ActivityLog::create([
                    'user_id' => $userId,
                    'action' => 'Tambah User',
                    'description' => "Menambahkan user baru: {$user->name} ({$data['role']})"
                ]);
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Create user error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateUser(User $user, array $data): User
    {
        try {
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $oldRole = $user->getRoleNames()->first();
            $user->update($data);

            if (isset($data['role']) && $data['role'] !== $oldRole) {
                $user->syncRoles([$data['role']]);
            }

            $userId = Auth::id();
            if ($userId) {
                ActivityLog::create([
                    'user_id' => $userId,
                    'action' => 'Update User',
                    'description' => "Memperbarui data user: {$user->name}"
                ]);
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Update user error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteUser(User $user, int $currentUserId): bool
    {
        if ($user->id == $currentUserId) {
            return false;
        }

        $userName = $user->name;
        $user->delete();

        $userId = Auth::id();
        if ($userId) {
            ActivityLog::create([
                'user_id' => $userId,
                'action' => 'Hapus User',
                'description' => "Menghapus user: {$userName}"
            ]);
        }

        return true;
    }

    public function getRoles(): array
    {
        return Role::pluck('name')->toArray();
    }
}
