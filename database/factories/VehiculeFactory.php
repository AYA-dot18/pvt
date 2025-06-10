<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Personnel;
use App\Models\Vehicule;

class VehiculeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicule::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'personnel_id' => Personnel::factory(),
            'matricule' => $this->faker->regexify('[A-Za-z0-9]{191}'),
            'puissance' => $this->faker->numberBetween(-10000, 10000),
        ];
    }
}
