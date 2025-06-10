<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deplacement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'trajet_id',
        'prime_id',
        'ligne_budgetaire_id',
        'etat_somme_id',
        'ik_id',
        'montant',
        'nombre_taux',
        'ordre_mission_path',
        'mois',
        'date_debut',
        'date_fin',
        'repas',
        'heure_depart',
        'heure_retour',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'trajet_id' => 'integer',
        'prime_id' => 'integer',
        
        'ligne_budgetaire_id' => 'integer',
        'etat_somme_id' => 'integer',
        'ik_id' => 'integer',
        'montant' => 'float',
        'nombre_taux' => 'integer',
        'mois' => 'integer',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'repas' => 'integer',
         'group_trip_id'=> 'integer',
    ];

    /**
     * Get the related EtatSomme.
     */
    public function etatSomme(): BelongsTo
    {
        return $this->belongsTo(EtatSomme::class);
    }

    /**
     * Get the related Trajet.
     */
    public function trajet(): BelongsTo
    {
        return $this->belongsTo(Trajet::class);
    }

    /**
     * Get the related LigneBudgetaire.
     */
    public function ligneBudgetaire(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class);
    }

    /**
     * Get the related Prime.
     */
    public function prime(): BelongsTo
    {
        return $this->belongsTo(Prime::class);
    }

    /**
     * Get the related IK.
     */
    public function ik(): HasOne
    {
        return $this->hasOne(Ik::class);
    }
    public function groupTrip()
{
    return $this->belongsTo(GroupTrip::class);
}

}
