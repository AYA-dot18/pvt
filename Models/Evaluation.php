<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'personnel_id',
        'prime_id',
        'resultat_deplacement',
        'organisation',
        'respect_horaires',
        'gestion_couts',
        'commentaire_deplacement',
        'ponctualite',
        'communication',
        'professionnalisme',
        'autonomie',
        'commentaire_personnel',
        'justificatif_path',
    ];

    public function personnel() {
        return $this->belongsTo(Personnel::class);
    }

    public function prime() {
        return $this->belongsTo(Prime::class);
    }
}
