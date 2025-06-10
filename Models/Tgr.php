<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tgr extends Model
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
        'tgr_path',
        'statut',
        'ligne_budgetaire_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'montant' => 'float',
        'ligne_budgetaire_id' => 'integer',
    ];

    public function deplacements(): HasMany
    {
        return $this->hasMany(Deplacement::class);
    }

    public function iks(): HasMany
    {
        return $this->hasMany(Ik::class);
    }

    public function ligneBudgetaire(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class);
    }
}
