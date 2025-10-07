<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // (opsional) hapus blok factory default kalau nggak perlu
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // >>> panggil seeder akun awal (pemilik & kasir)
        $this->call(UserRoleSeeder::class);
    }
}
