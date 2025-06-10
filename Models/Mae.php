<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mae extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personnel_id',
        'date_depart',
        'date_retour',
        'destination',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'personnel_id' => 'integer',
        'date_depart' => 'date:Y-m-d',
        'date_retour' => 'date:Y-m-d',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }
}
