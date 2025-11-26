<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'description',
        'type',
        'is_active',
        'is_recurring',
        'color',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    /**
     * Scope untuk holiday yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk holiday di tahun tertentu
     */
    public function scopeInYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }

    /**
     * Scope untuk holiday yang akan datang
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->startOfDay())
                     ->orderBy('date', 'asc');
    }

    /**
     * Scope untuk holiday hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now());
    }

    /**
     * Get formatted date
     */
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date->locale('id')->translatedFormat('d F Y')
        );
    }

    /**
     * Get type label in Indonesian
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->type) {
                'national' => 'Libur Nasional',
                'religious' => 'Hari Keagamaan',
                'custom' => 'Libur Khusus',
                default => 'Lainnya',
            }
        );
    }

    /**
     * Check if this holiday is today
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Check if date is a holiday
     */
    public static function isHoliday($date): bool
    {
        return static::active()
            ->whereDate('date', $date)
            ->exists();
    }

    /**
     * Get holiday for specific date
     */
    public static function getHoliday($date)
    {
        return static::active()
            ->whereDate('date', $date)
            ->first();
    }
}
