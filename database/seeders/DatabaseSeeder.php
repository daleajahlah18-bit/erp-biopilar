<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
    public function run()
    {
        // Membuat akun admin default
        User::firstOrCreate(
            ['email' => 'admin@biopilar.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
