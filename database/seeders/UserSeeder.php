<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'telephone' => '+221771234567',
            'nci' => '1234567890123',
            'adresse' => 'Dakar, Sénégal',
            'role' => 'admin',
        ]);

        // Créer un utilisateur client
        User::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => Hash::make('password123'),
            'telephone' => '+221771234568',
            'nci' => '1234567890124',
            'adresse' => 'Dakar, Sénégal',
            'role' => 'client',
        ]);

        // Créer quelques utilisateurs supplémentaires pour les tests
        User::factory(5)->create([
            'role' => 'client',
        ]);
    }
}