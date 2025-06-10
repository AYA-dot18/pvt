<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use DateTime;
use Illuminate\Support\Str;
use App\Models\Mae;
use App\Models\Personnel;

class MaeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mae::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'personnel_id' => Personnel::inRandomOrder()->value('id') ?? Personnel::factory(),
            'date_depart' => $dateDepart = $this->faker->dateTimeBetween('2025-01-01', '2025-12-01')->format('Y-m-d'),
            'date_retour' => (new DateTime($dateDepart))->modify('+'.rand(1, 30).' days')->format('Y-m-d'),
            'destination' => $this->faker->country(),
        ];
    }
}
