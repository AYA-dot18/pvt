<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Personnel;

class PersonnelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Personnel::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'num_cin' => $this->faker->randomNumber(7, true),
            'num_ppr' => $this->faker->randomNumber(6, true),
            'grade' => $this->faker->randomElement(['Administrateur 1er Grade','Administrateur 2ème Grade','Ingénieur 1er Grade','Ingénieur Grade Principal','Ingénieur en Chef 1er Grade','Ingénieur en Chef Grade Principal','Technicien 3eme Grade','Technicien 2eme Grade']),
            'echelle' => $this->faker->numberBetween(5, 12),
            'groupe' => $this->faker->randomElement(['I','II']),
            'taux_indemnite' => $this->faker->randomElement([40,60,80,100]),
            'montant_indemnite' => $this->faker->randomElement([1040,1360,3500,8500,10000]),
            'banque_rib' => $this->faker->randomNumber(3, true),
            'guichet_rib' => $this->faker->randomNumber(3, true),
            'num_compte_rib' => $this->faker->numberBetween(1000000000000000, 9999999999999999),
            'code_rib' => $this->faker->randomNumber(2, true),
            'residence' => $this->faker->randomElement(['CASABLANCA','RABAT','SAFI','AGADIR','NADOR','EL JADIDA','TANGER','M\'DIQ']),
            'statut' => $this->faker->randomElement([0,1]),
            'suffix' => $this->faker->regexify('Mr','Mme'),
            'creance' => $this->faker->randomElement([0,20,40,60,-20,-40,-60]),
            'situation_familiale' => $this->faker->regexify('Marié','Mariée','Divorcé','Divorcée','Veuf','Veuve'),
        ];
    }
}
