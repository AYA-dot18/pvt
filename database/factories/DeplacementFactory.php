<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Deplacement;
use App\Models\LigneBudgetaire;
use App\Models\Prime;
use App\Models\Trajet;
use App\Models\Vehicule;

class DeplacementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Deplacement::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'trajet_id' => Trajet::factory(),
            'prime_id' => Prime::factory(),
            'ligne_budgetaire_id' => LigneBudgetaire::factory(),
            'montant' => $this->faker->randomFloat(0, 0, 9999999999.),
            'ordre_mission_path' => $this->faker->text(),
            'mois' => $this->faker->numberBetween(-10000, 10000),
            'date_depart' => $this->faker->date(),
            'date_retour' => $this->faker->date(),
            'ik' => $this->faker->boolean(),
            'ik_heritee' => $this->faker->boolean(),
            'heure_aller' => $this->faker->time(),
            'heure_retour' => $this->faker->time(),
            'vehicule_id' => Vehicule::factory(),
        ];
    }
}
