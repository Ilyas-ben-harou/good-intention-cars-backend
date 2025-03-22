<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Car;

class Technical_visit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'car_id',
        'visit_date',
        'expiration_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visit_date' => 'date',
        'expiration_date' => 'date',
    ];

    /**
     * Get the car that owns the technical visit.
     */
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Scope a query to only include active technical visits.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('expiration_date', '>=', now());
    }

    /**
     * Scope a query to only include expired technical visits.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now());
    }

    /**
     * Scope a query to only include technical visits expiring soon (within 30 days).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiration_date', '>=', now())
                    ->where('expiration_date', '<=', now()->addDays($days));
    }

    /**
     * Check if the technical visit is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->expiration_date >= now();
    }

    /**
     * Get the remaining days until expiration.
     *
     * @return int
     */
    public function daysUntilExpiration()
    {
        if (!$this->isActive()) {
            return 0;
        }
        
        return now()->diffInDays($this->expiration_date, false);
    }
}