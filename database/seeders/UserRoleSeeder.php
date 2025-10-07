<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Owner
        User::updateOrCreate(
            ['email' => 'pemilik@mail.com'],
            [
                'name'     => 'Pemilik',
                'password' => '12345',   // akan di-hash otomatis (casts: hashed)
                'role'     => 'pemilik',
            ]
        );

        // Kasir
        User::updateOrCreate(
            ['email' => 'kasir@mail.com'],
            [
                'name'     => 'Kasir',
                'password' => '12345',   // akan di-hash otomatis
                'role'     => 'kasir',
            ]
        );
    }
}
