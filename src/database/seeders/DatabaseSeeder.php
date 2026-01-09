<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        User::create([
            'name' => '管理者A',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => '一般太郎',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role_id' => User::ROLE_USER,
            'email_verified_at' => now(),
        ]);
    }
}
