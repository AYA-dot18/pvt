<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Trajet;

class TrajetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Trajet::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ville' => $this->faker->regexify('[A-Za-z0-9]{191}'),
            'aller' => $this->faker->regexify('[A-Za-z0-9]{191}'),
            'retour' => $this->faker->regexify('[A-Za-z0-9]{191}'),
            'trajet' => $this->faker->regexify('[A-Za-z0-9]{191}'),
            'km_route' => $this->faker->numberBetween(-10000, 10000),
            'km_piste' => $this->faker->numberBetween(-10000, 10000),
        ];
    }
}
