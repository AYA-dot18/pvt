<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Personnel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nom',
        'prenom',
        'num_cin',
        'num_ppr',
        'grade',
        'echelle',
        'groupe',
        'taux_indemnite',
        'montant_indemnite',
        'banque_rib',
        'guichet_rib',
        'num_compte_rib',
        'code_rib',
        'residence',
        'statut',
        'suffix',
        'creance',
        'situation_familiale',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'montant_indemnite' => 'float',
        'num_compte_rib' => 'string',
        'creance' => 'float',
    ];

    protected $appends = ['fullname'];

    public function getFullnameAttribute(): string
    {
        return "{$this->nom} {$this->prenom}";
    }

    public function vehicule(): HasOne
    {
        return $this->hasOne(Vehicule::class);
    }

    public function kmParcourus(): HasMany
    {
        return $this->hasMany(KmParcouru::class);
    }

    public function maes(): HasMany
    {
        return $this->hasMany(Mae::class);
    }

    public function conges(): HasMany
    {
        return $this->hasMany(Conge::class);
    }

    public function primes(): HasMany
    {
        return $this->hasMany(Prime::class);
 
    }

}
