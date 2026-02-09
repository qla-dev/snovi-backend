<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(InitialContentSeeder::class);

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('Sarajevo2026!'),
                'api_token' => \Illuminate\Support\Str::random(60),
            ]
        );
    }
}
