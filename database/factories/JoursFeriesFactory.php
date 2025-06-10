<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\JoursFeries;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JoursFeries>
 */
class JoursFeriesFactory extends Factory
{
   /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JoursFeries::class;

    public function definition(): array
    {
        return [
            'date' => $this->faker->dateTimeBetween('first day of January this year', 'last day of December this year'),
            'titre' => $this->faker->word()
        ];
    }
}
