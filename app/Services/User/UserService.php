<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
// Hapus import Spatie Role jika tidak digunakan
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

            // ✅ Simpan langsung ke kolom role (Manual)
            $user = User::create($data);

            $userId = Auth::id();
            if ($userId) {
                ActivityLog::create([
                    'user_id' => $userId,
                    'action' => 'Tambah User',
                    'description' => "Menambahkan user baru: {$user->name} ({$user->role})"
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

            // ✅ Update data termasuk kolom role secara manual
            $user->update($data);

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

    public function deleteUser($user, $currentUserId)
    {
        // 1. Validasi tidak boleh hapus diri sendiri
        if ((int)$user->id === (int)$currentUserId) {
            return 'self';
        }

        // 2. Validasi jika user punya pesanan/transaksi
        // (Kita cek apakah ada data user_id ini di tabel loans)
        $hasLoans = \App\Models\Loan::where('user_id', $user->id)->exists();
        if ($hasLoans) {
            return 'has_loans';
        }

        // 3. Jika aman, hapus user
        try {
            $user->delete();
            return 'success';
        } catch (\Exception $e) {
            Log::error('Delete user error: ' . $e->getMessage());
            return 'error';
        }
    }
    
    public function getRoles(): array
    {
        // ✅ Karena manual, kita bisa mengembalikan array statis role yang tersedia
        return ['admin', 'petugas', 'peminjam'];
    }
}
