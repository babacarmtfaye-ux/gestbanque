<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PassportClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un client personnel pour l'accès aux tokens
        DB::table('oauth_clients')->insert([
            'id' => 1,
            'user_id' => null,
            'name' => 'GestBanque Personal Access Client',
            'secret' => Str::random(40),
            'provider' => null,
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un client pour le password grant
        DB::table('oauth_clients')->insert([
            'id' => 2,
            'user_id' => null,
            'name' => 'GestBanque Password Grant Client',
            'secret' => Str::random(40),
            'provider' => 'users',
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Passport clients created successfully!');
    }
}
