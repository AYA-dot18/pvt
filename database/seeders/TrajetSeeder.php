<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Trajet;

class TrajetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Trajet::create([
            'ville' => 'NADOR',
            'aller' => 'CASABLANCA-NADOR',
            'retour' => 'NADOR-CASABLANCA',
            'trajet' => 'CASABLANCA-NADOR-CASABLANCA',
            'km_route' => 600,
            'km_piste' => 0,
        ]);
        Trajet::create([
            'ville' => 'TANGER',
            'aller' => 'CASABLANCA-TANGER',
            'retour' => 'TANGER-CASABLANCA',
            'trajet' => 'CASABLANCA-TANGER-CASABLANCA',
            'km_route' => 350,
            'km_piste' => 0,
        ]);
        Trajet::create([
            'ville' => 'M\'DIQ',
            'aller' => 'CASABLANCA-M\'DIQ',
            'retour' => 'M\'DIQ-CASABLANCA',
            'trajet' => 'CASABLANCA-M\'DIQ-CASABLANCA',
            'km_route' => 410,
            'km_piste' => 0,
        ]);
        Trajet::create([
            'ville' => 'AGADIR',
            'aller' => 'CASABLANCA-AGADIR',
            'retour' => 'AGADIR-CASABLANCA',
            'trajet' => 'CASABLANCA-AGADIR-CASABLANCA',
            'km_route' => 500,
            'km_piste' => 0,
        ]);
        Trajet::create([
            'ville' => 'RABAT',
            'aller' => 'CASABLANCA-RABAT',
            'retour' => 'RABAT-CASABLANCA',
            'trajet' => 'CASABLANCA-RABAT-CASABLANCA',
            'km_route' => 90,
            'km_piste' => 0,
        ]);
        Trajet::create([
            'ville' => 'SAFI',
            'aller' => 'CASABLANCA-SAFI',
            'retour' => 'SAFI-CASABLANCA',
            'trajet' => 'CASABLANCA-SAFI-CASABLANCA',
            'km_route' => 240,
            'km_piste' => 0,
        ]);
        Trajet::create([
            'ville' => 'LAAYOUNE',
            'aller' => 'CASABLANCA-LAAYOUNE',
            'retour' => 'LAAYOUNE-CASABLANCA',
            'trajet' => 'CASABLANCA-LAAYOUNE-CASABLANCA',
            'km_route' => 1200,
            'km_piste' => 0,
        ]);
        Trajet::create([
            'ville' => 'DAKHLA',
            'aller' => 'CASABLANCA-DAKHLA',
            'retour' => 'DAKHLA-CASABLANCA',
            'trajet' => 'CASABLANCA-DAKHLA-CASABLANCA',
            'km_route' => 1650,
            'km_piste' => 0,
        ]);
        Trajet::create([
            'ville' => 'EL JADIDA',
            'aller' => 'CASABLANCA-EL JADIDA',
            'retour' => 'EL JADIDA-CASABLANCA',
            'trajet' => 'CASABLANCA-EL JADIDA-CASABLANCA',
            'km_route' => 100,
            'km_piste' => 0,
        ]);
    }
}
