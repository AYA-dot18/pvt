<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\KmParcouru;
use App\Models\Personnel;

class KmParcouruFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = KmParcouru::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'personnel_id' => Personnel::factory(),
            'annee' => $this->faker->numberBetween(-10000, 10000),
            'km_parcourus' => $this->faker->numberBetween(-10000, 10000),
        ];
    }
}
