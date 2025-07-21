<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Joe Lohr',
            'email' => 'emailme@joelohr.com',
            'password' => bcrypt('password'),
            'role' => Role::PARENT,
        ]);

        User::factory()->create([
            'name' => 'Sherry Lohr',
            'email' => 'sherryalohr@email.com',
            'password' => bcrypt('password'),
            'role' => Role::PARENT,
        ]);

        User::factory()->create([
            'name' => 'Kailee Lohr',
            'email' => 'kaileelohr@gmail.com',
            'password' => bcrypt('password'),
            'role' => Role::CHILD,
        ]);

        User::factory()->create([
            'name' => 'Becca Lohr',
            'email' => 'beccalohr@gmail.com',
            'password' => bcrypt('password'),
            'role' => Role::CHILD,
        ]);

        User::factory()->create([
            'name' => 'Alissa Lohr',
            'email' => 'alissalohr@gmail.com',
            'password' => bcrypt('password'),
            'role' => Role::CHILD,
        ]);

        User::factory()->create([
            'name' => 'Jacob Lohr',
            'email' => 'jacoblohr@gmail.com',
            'password' => bcrypt('password'),
            'role' => Role::CHILD,
        ]);
    }
}
