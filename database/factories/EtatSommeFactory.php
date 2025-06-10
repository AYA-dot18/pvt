<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\EtatSomme;
use App\Models\Vehicule;

class EtatSommeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EtatSomme::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'montant' => $this->faker->randomFloat(0, 0, 9999999999.),
            'type' => $this->faker->randomElement(["ik","prime"]),
            'vehicule_id' => Vehicule::factory(),
            'etat_sommes_path' => $this->faker->text(),
            'mois' => $this->faker->numberBetween(-10000, 10000),
        ];
    }
}
