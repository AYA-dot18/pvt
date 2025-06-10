<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ik extends Model
{
    use HasFactory;

    protected $fillable = [
        'deplacement_id',
        'prime_id',
        'tgr_id',
        'montant',
        'mois',
        'ligne_budgetaire_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'montant' => 'float',
        'mois' => 'integer',
        'tgr_id' => 'integer',
        'deplacement_id' => 'integer',
    ];
    /**
     * Get the related Deplacement.
     */
    public function deplacement(): BelongsTo
    {
        return $this->belongsTo(Deplacement::class);
    }

    /**
     * Get the related Deplacement.
     */
    public function tgr(): BelongsTo
    {
        return $this->belongsTo(Tgr::class);
    }
}
