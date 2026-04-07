<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Buat roles untuk guard api (sesuai dengan auth Anda)
        $guards = ['api', 'web']; // Support kedua guard

        foreach ($guards as $guard) {
            Role::firstOrCreate(
                ['name' => 'admin', 'guard_name' => $guard],
                ['name' => 'admin', 'guard_name' => $guard]
            );

            Role::firstOrCreate(
                ['name' => 'petugas', 'guard_name' => $guard],
                ['name' => 'petugas', 'guard_name' => $guard]
            );

            Role::firstOrCreate(
                ['name' => 'peminjam', 'guard_name' => $guard],
                ['name' => 'peminjam', 'guard_name' => $guard]
            );
        }

        $this->command->info('Roles seeded successfully for api and web guards!');
    }
}
