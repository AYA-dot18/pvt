<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Conge extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personnel_id',
        'date_debut',
        'date_fin',
        'type',
        'remarque'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'personnel_id' => 'integer',
        'date_debut' => 'date:Y-m-d',
        'date_fin' => 'date:Y-m-d',
        'remarque' => 'string'
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }
}
