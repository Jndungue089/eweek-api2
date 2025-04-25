<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'fullName' => 'Zélio Papel',
            'school' => 'none',
            'grade' => 'none',
            'age_range' => 'none',
            'course' => 'none',
            'email' => 'imperador@gmail.com',
            'phone' => '900000000',
            'gender' => 'Masculino',
            'profile' => 'admin',
            'password' => Hash::make("12345678"),
        ]);
    }
}
