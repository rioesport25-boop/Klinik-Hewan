<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Appointment extends Model
{
    protected $fillable = [
        'booking_code',
        'user_id',
        'pet_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'end_time',
        'status',
        'complaint',
        'diagnosis',
        'treatment',
        'prescription',
        'notes',
        'total_cost',
        'loyalty_points_earned',
        'checked_in_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'total_cost' => 'decimal:2',
        'checked_in_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate booking code
        static::creating(function ($appointment) {
            if (!$appointment->booking_code) {
                $appointment->booking_code = 'BK-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'appointment_services')
            ->withPivot(['price', 'quantity', 'notes'])
            ->withTimestamps();
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function loyaltyPointTransactions()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', now()->toDateString());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }

    // Attributes
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                'pending' => 'Menunggu Konfirmasi',
                'confirmed' => 'Dikonfirmasi',
                'in_progress' => 'Sedang Berlangsung',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'no_show' => 'Tidak Hadir',
                default => $this->status,
            }
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                'pending' => 'warning',
                'confirmed' => 'info',
                'in_progress' => 'primary',
                'completed' => 'success',
                'cancelled' => 'danger',
                'no_show' => 'danger',
                default => 'secondary',
            }
        );
    }

    // Methods
    public function canReschedule(): bool
    {
        if (!in_array($this->status, ['pending', 'confirmed'])) {
            return false;
        }
        
        // Bisa reschedule max H-2
        return $this->appointment_date->greaterThan(now()->addDays(2));
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canReview(): bool
    {
        return $this->status === 'completed' && !$this->review;
    }

    public function isUpcoming(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) 
            && $this->appointment_date >= now()->toDateString();
    }

    public function isPast(): bool
    {
        return $this->appointment_date < now()->toDateString();
    }
}
