<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numeroCompte' => 'C' . $this->faker->unique()->numberBetween(1000000, 9999999),
            'titulaire' => $this->faker->name(),
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde' => $this->faker->randomFloat(2, 0, 10000000),
            'devise' => 'FCFA',
            'dateCreation' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'statut' => $this->faker->randomElement(['actif', 'bloque', 'ferme']),
            'motifBlocage' => $this->faker->optional(0.3)->sentence(),
            'user_id' => \App\Models\User::factory(),
            'metadata' => [
                'derniereModification' => now()->toISOString(),
                'version' => 1
            ],
        ];
    }
}
