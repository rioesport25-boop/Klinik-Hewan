<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'appointment_id',
        'doctor_id',
        'rating',
        'comment',
        'service_quality',
        'cleanliness',
        'friendliness',
        'is_visible',
        'verified_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_visible' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    protected function qualityLabel(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match($value) {
                'excellent' => 'Sangat Baik',
                'good' => 'Baik',
                'average' => 'Cukup',
                'poor' => 'Kurang',
                default => '-',
            }
        );
    }

    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }
}
