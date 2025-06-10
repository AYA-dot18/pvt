<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prime extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personnel_id',
        'montant_initial',
        'montant',
        'nombre_taux',
        'changetaux',
        'type',
        'creance_cree',
        'ancienne_creance',
        'nouvelle_creance',
        'ik',
        'remarque',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'personnel_id' => 'integer',
        'montant_initial' => 'float',
        'montant' => 'float',
        'nombre_taux' => 'integer',
        'creance_cree' => 'float',
        'ancienne_creance' => 'float',
        'nouvelle_creance' => 'float',
        'ik' => 'boolean',
    ];

    /**
     * Get the personnel that owns the prime.
     */
    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    /**
     * Get the mois_pvts for the prime.
     */
    public function moisPvts(): HasMany
    {
        return $this->hasMany(MoisPvt::class);
    }

    /**
     * Get the deplacements for the prime.
     */
    public function deplacements(): HasMany
    {
        return $this->hasMany(Deplacement::class);
    }
}
