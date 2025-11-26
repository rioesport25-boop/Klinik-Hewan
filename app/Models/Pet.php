<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Pet extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'species',
        'breed',
        'birth_date',
        'weight',
        'gender',
        'color',
        'photo',
        'medical_history',
        'allergies',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->birth_date) return null;
                return $this->birth_date->diffInYears(now());
            }
        );
    }

    protected function ageFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->birth_date) return 'Tidak diketahui';
                $years = $this->birth_date->diffInYears(now());
                $months = $this->birth_date->copy()->addYears($years)->diffInMonths(now());
                
                if ($years > 0) {
                    return $months > 0 ? "{$years} tahun {$months} bulan" : "{$years} tahun";
                }
                return $months > 0 ? "{$months} bulan" : "< 1 bulan";
            }
        );
    }

    protected function speciesLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->species) {
                'dog' => 'Anjing',
                'cat' => 'Kucing',
                'bird' => 'Burung',
                'rabbit' => 'Kelinci',
                'hamster' => 'Hamster',
                default => 'Lainnya',
            }
        );
    }

    protected function genderLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->gender) {
                'male' => 'Jantan',
                'female' => 'Betina',
                default => '-',
            }
        );
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->photo ? asset('storage/' . $this->photo) : null
        );
    }
}
