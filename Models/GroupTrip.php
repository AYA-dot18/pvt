<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GroupTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'lieu',
        'date_depart',
        'date_retour',
        'type_mission',
        'moyen_transport',
        'cout_total_estime',
    ];

    protected $dates = ['date_depart', 'date_retour'];

    public function participants()
    {
        return $this->belongsToMany(User::class, 'group_trip_user')
                    ->withPivot('role', 'distance_parcourue', 'prime_calculee')
                    ->withTimestamps();
    }

    // 👇 Bonus : calcul du nombre de jours automatiquement
    public function getNombreJoursAttribute()
    {
        return Carbon::parse($this->date_depart)->diffInDays(Carbon::parse($this->date_retour)) + 1;
    }
    public function deplacements()
{
    return $this->hasMany(Deplacement::class);
}

}
