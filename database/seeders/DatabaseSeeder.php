<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Compte;
use App\Models\Transaction;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Créer un client
        User::factory()->create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'role' => 'client',
        ]);

        // Créer 3 autres utilisateurs clients
        User::factory()->count(3)->create([
            'role' => 'client',
        ]);

        // Appeler les autres seeders
        $this->call([
            CompteSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
