<?php

namespace App\Filament\Resources\SliderImageResource\Pages;

use App\Filament\Resources\SliderImageResource;
use App\Models\SliderImage;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSliderImages extends ListRecords
{
    protected static string $resource = SliderImageResource::class;

    protected function getHeaderActions(): array
    {
        $totalSliders = SliderImage::count();
        $canCreate = $totalSliders < 5;

        return [
            Actions\CreateAction::make()
                ->disabled(!$canCreate)
                ->tooltip($canCreate ? null : 'Maksimal 5 gambar slider')
                ->label('Tambah Slider (' . $totalSliders . '/5)'),
        ];
    }
}
