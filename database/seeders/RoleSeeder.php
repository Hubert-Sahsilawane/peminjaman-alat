<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Cek dan buat role jika belum ada
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }

        if (!Role::where('name', 'petugas')->exists()) {
            Role::create(['name' => 'petugas']);
        }

        if (!Role::where('name', 'peminjam')->exists()) {
            Role::create(['name' => 'peminjam']);
        }

        $this->command->info('Roles seeded successfully!');
    }
}
