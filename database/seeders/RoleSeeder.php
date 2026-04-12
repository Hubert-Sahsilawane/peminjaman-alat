<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar role yang akan dibuat
        $roles = ['admin', 'petugas', 'peminjam'];
        $guards = ['web', 'api'];

        foreach ($guards as $guard) {
            foreach ($roles as $roleName) {
                Role::firstOrCreate(
                    ['name' => $roleName, 'guard_name' => $guard],
                    ['name' => $roleName, 'guard_name' => $guard]
                );
            }
        }

        // Buat user Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@app.com'],
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('password'),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Buat user Petugas
        $petugas = User::firstOrCreate(
            ['email' => 'petugas@app.com'],
            [
                'name' => 'Petugas Lab',
                'password' => Hash::make('password'),
            ]
        );
        if (!$petugas->hasRole('petugas')) {
            $petugas->assignRole('petugas');
        }

        // Buat user Peminjam
        $peminjam = User::firstOrCreate(
            ['email' => 'siswa@app.com'],
            [
                'name' => 'Siswa 1',
                'password' => Hash::make('password'),
            ]
        );
        if (!$peminjam->hasRole('peminjam')) {
            $peminjam->assignRole('peminjam');
        }

        $this->command->info('Roles and users seeded successfully!');
    }
}
