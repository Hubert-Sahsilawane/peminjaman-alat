<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat roles jika belum ada
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'petugas']);
        Role::firstOrCreate(['name' => 'peminjam']);

        // Buat user Admin (cek dulu apakah sudah ada)
        $admin = User::firstOrCreate(
            ['email' => 'admin@app.com'],
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('password'),
                'role' => 'admin',
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
                'role' => 'petugas',
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
                'role' => 'peminjam',
            ]
        );
        if (!$peminjam->hasRole('peminjam')) {
            $peminjam->assignRole('peminjam');
        }
    }
}
