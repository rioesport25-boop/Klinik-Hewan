<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            // Libur Nasional 2025
            [
                'name' => 'Tahun Baru 2025',
                'date' => '2025-01-01',
                'description' => 'Tahun Baru Masehi',
                'type' => 'national',
                'is_active' => true,
                'is_recurring' => true,
                'color' => '#dc2626',
            ],
            [
                'name' => 'Tahun Baru Imlek 2576',
                'date' => '2025-01-29',
                'description' => 'Tahun Baru Imlek - Tahun Ular Kayu',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#dc2626',
            ],
            [
                'name' => 'Isra Miraj Nabi Muhammad SAW',
                'date' => '2025-02-14',
                'description' => 'Peringatan Isra Miraj',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#16a34a',
            ],
            [
                'name' => 'Hari Raya Nyepi 1947',
                'date' => '2025-03-29',
                'description' => 'Tahun Baru Saka',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#ea580c',
            ],
            [
                'name' => 'Wafat Isa Almasih',
                'date' => '2025-04-18',
                'description' => 'Jumat Agung',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#7c3aed',
            ],
            [
                'name' => 'Hari Raya Idul Fitri 1446 H',
                'date' => '2025-03-31',
                'description' => 'Hari Raya Idul Fitri - Hari Pertama',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#16a34a',
            ],
            [
                'name' => 'Hari Raya Idul Fitri 1446 H',
                'date' => '2025-04-01',
                'description' => 'Hari Raya Idul Fitri - Hari Kedua',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#16a34a',
            ],
            [
                'name' => 'Hari Buruh Internasional',
                'date' => '2025-05-01',
                'description' => 'May Day',
                'type' => 'national',
                'is_active' => true,
                'is_recurring' => true,
                'color' => '#dc2626',
            ],
            [
                'name' => 'Kenaikan Isa Almasih',
                'date' => '2025-05-29',
                'description' => 'Hari Kenaikan Yesus Kristus',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#7c3aed',
            ],
            [
                'name' => 'Hari Raya Waisak 2569',
                'date' => '2025-05-12',
                'description' => 'Peringatan Trisuci Waisak',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#ea580c',
            ],
            [
                'name' => 'Hari Lahir Pancasila',
                'date' => '2025-06-01',
                'description' => 'Hari Lahirnya Pancasila',
                'type' => 'national',
                'is_active' => true,
                'is_recurring' => true,
                'color' => '#dc2626',
            ],
            [
                'name' => 'Hari Raya Idul Adha 1446 H',
                'date' => '2025-06-07',
                'description' => 'Hari Raya Idul Adha',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#16a34a',
            ],
            [
                'name' => 'Tahun Baru Islam 1447 H',
                'date' => '2025-06-27',
                'description' => 'Tahun Baru Hijriyah',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#16a34a',
            ],
            [
                'name' => 'Hari Kemerdekaan RI',
                'date' => '2025-08-17',
                'description' => 'Hari Proklamasi Kemerdekaan Indonesia',
                'type' => 'national',
                'is_active' => true,
                'is_recurring' => true,
                'color' => '#dc2626',
            ],
            [
                'name' => 'Maulid Nabi Muhammad SAW',
                'date' => '2025-09-05',
                'description' => 'Peringatan Maulid Nabi Muhammad',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#16a34a',
            ],
            [
                'name' => 'Hari Natal',
                'date' => '2025-12-25',
                'description' => 'Perayaan Kelahiran Yesus Kristus',
                'type' => 'religious',
                'is_active' => true,
                'is_recurring' => true,
                'color' => '#dc2626',
            ],
            // Tambahan: Cuti Bersama (bisa disesuaikan)
            [
                'name' => 'Cuti Bersama Idul Fitri',
                'date' => '2025-03-28',
                'description' => 'Cuti bersama menjelang Idul Fitri',
                'type' => 'custom',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#0891b2',
            ],
            [
                'name' => 'Cuti Bersama Idul Fitri',
                'date' => '2025-04-02',
                'description' => 'Cuti bersama setelah Idul Fitri',
                'type' => 'custom',
                'is_active' => true,
                'is_recurring' => false,
                'color' => '#0891b2',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}
