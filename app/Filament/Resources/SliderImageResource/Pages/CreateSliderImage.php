<?php

namespace App\Filament\Resources\SliderImageResource\Pages;

use App\Filament\Resources\SliderImageResource;
use App\Models\SliderImage;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSliderImage extends CreateRecord
{
    protected static string $resource = SliderImageResource::class;

    protected function beforeValidate(): void
    {
        $totalSliders = SliderImage::count();

        if ($totalSliders >= 5) {
            Notification::make()
                ->title('Batas maksimal tercapai')
                ->body('Anda hanya dapat menambahkan maksimal 5 gambar slider. Hapus gambar yang ada terlebih dahulu.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
