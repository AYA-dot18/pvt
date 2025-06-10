<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use DateTime;
use App\Models\Conge;
use App\Models\Personnel;

class CongeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Conge::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'personnel_id' => Personnel::inRandomOrder()->value('id') ?? Personnel::factory(),
            'date_debut' => $dateDepart = $this->faker->dateTimeBetween('2025-01-01', '2025-12-01')->format('Y-m-d'),
            'date_fin' => (new DateTime($dateDepart))->modify('+'.rand(1, 30).' days')->format('Y-m-d'),
            'type' => $this->faker->randomElement(['Maladie','Administratif']),
            'remarque' => $this->faker->word(),
        ];
    }
}
