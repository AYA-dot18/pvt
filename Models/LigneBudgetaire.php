<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LigneBudgetaire extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nom',
        'type',
        'exercice',
        'chapitre',
        'article',
        'paragraphe',
        'programme',
        'region',
        'projet',
        'ligne',
        'ligne_budgetaire',
        'montant_initial',
        'montant_restant'
    ];

    // Automatically set "ligne_budgetaire" before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->setLigneBudgetaire();
        });
    }

    public function setLigneBudgetaire()
    {
        if ($this->type === 'IK') {
            $this->ligne_budgetaire = implode('', [
                $this->chapitre,
                $this->programme,
                $this->region,
                $this->projet,
                $this->ligne
            ]);
        } else {
            $this->ligne_budgetaire = implode('', [
                $this->chapitre,
                $this->article,
                $this->paragraphe,
                $this->ligne
            ]);
        }
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'ligne_budgetaire' => 'string'
    ];

    public function tgrs(): HasMany
    {
        return $this->hasMany(Tgr::class);
    }
}
