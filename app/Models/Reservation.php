<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $table = 'reservations';
    
    protected $fillable = [
        'client_nom_complete',
        'client_phone',
        'car_id',
        'date_debut',
        'pickup_location',
        'dropoff_location',
        'date_fin',
        'montantTotal',
        'gps',
        'baby_seat',
        'status_client',
        'status',
        'payment_status',
    ];
    
    // Relation avec la voiture
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
}
