<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\LigneBudgetaire;
use App\Models\Tgr;

class TgrFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tgr::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'montant' => $this->faker->randomFloat(0, 0, 9999999999.),
            'type' => $this->faker->randomElement(["ik","prime"]),
            'tgr_path' => $this->faker->text(),
            'ligne_budgetaire_id' => LigneBudgetaire::factory(),
        ];
    }
}
