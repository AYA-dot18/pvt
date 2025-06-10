<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\LigneBudgetaire;

class LigneBudgetaireFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LigneBudgetaire::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->word(),
            'type' => $this->faker->randomElement(['IK','PR','PVT']),
            'exercice' => $this->faker->randomElement([2024,2025]),
            'chapitre' => $this->faker->numberBetween(1000000000, 9999999999),
            'article' => $this->faker->randomNumber(5, true),
            'paragraphe' => $this->faker->randomNumber(2, true),
            'ligne' => $this->faker->randomNumber(2, true),
            'programme' => $this->faker->randomNumber(3, true),
            'region' => $this->faker->randomNumber(2, true),
            'projet' => $this->faker->randomNumber(2, true),
            'ligne' => $this->faker->randomNumber(2, true),
        ];
    }
}
