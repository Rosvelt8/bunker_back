<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Tableau de pays francophones
        $countries = [
            'France',
            'Belgique',
            'Suisse',
            'Canada',
            'Luxembourg',
            'Monaco',
            'Sénégal',
            'Côte d\'Ivoire',
            'Mali',
            'Cameroun',
            'Bénin',
            'Burkina Faso',
            'Congo',
            'Gabon',
            'Madagascar'
        ];

        return [
            'name' => fake()->unique()->randomElement($countries)
        ];
    }
}
