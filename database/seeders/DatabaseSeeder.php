<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@app.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Petugas Lab',
            'email' => 'petugas@app.com',
            'password' => Hash::make('password'),
            'role' => 'petugas'
        ]);

        User::create([
            'name' => 'Siswa 1',
            'email' => 'siswa@app.com',
            'password' => Hash::make('password'),
            'role' => 'peminjam'
        ]);
    }
}
