<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'marque',
        'modele',
        'immatriculation',
        'dors',
        'engine_capacity',
        'fuel_type',
        'type',
        'passengers',
        'prixByDay',
        'disponibilite',
        'description',
        'photos'
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    public function assurances()
    {
        return $this->hasMany(Assurance::class);
    }


    public function technicalVisits()
    {
        return $this->hasMany(Technical_visit::class);
    }

    /**
     * Get the active technical visit for the car.
     */
    public function activeVisit()
    {
        return $this->hasOne(Technical_visit::class)->where('expiration_date', '>=', now())
            ->orderBy('expiration_date', 'asc');
    }
}
