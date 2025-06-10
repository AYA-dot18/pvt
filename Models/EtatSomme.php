<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EtatSomme extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'montant',
        'type',
        'vehicule_id',
        'etat_somme_path',
        'mois',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'montant' => 'float',
        'vehicule_id' => 'integer',
    ];

    public function deplacements(): HasMany
    {
        return $this->hasMany(Deplacement::class);
    }

    public function vehicule(): HasOne
    {
        return $this->hasOne(Vehicule::class);
    }

}
