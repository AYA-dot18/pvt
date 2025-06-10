<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Personnel;
use App\Models\Prime;

class PrimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Prime::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'personnel_id' => Personnel::factory(),
            'montant' => $this->faker->randomFloat(0, 0, 9999999999.),
            'nombre_taux' => $this->faker->randomNumber(),
            'type' => $this->faker->regexify('[A-Za-z0-9]{191}'),
            'ik' => $this->faker->boolean(),
        ];
    }
}
