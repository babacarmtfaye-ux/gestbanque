<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();
        $admin = $users->where('role', 'admin')->first();
        $clients = $users->where('role', 'client');

        \App\Models\Compte::factory(10)->create([
            'user_id' => $admin->id,
        ]);

        foreach ($clients as $client) {
            \App\Models\Compte::factory(2)->create([
                'user_id' => $client->id,
            ]);
        }
    }
}
