<?php

namespace App\Filament\Pages;

use App\Models\FooterSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ImagesHeader extends Page
{
    use \Filament\Forms\Concerns\InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static string $view = 'filament.pages.images-header';

    protected static ?string $navigationLabel = 'Images Header';

    protected static ?string $navigationGroup = 'Website Settings';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = FooterSetting::getSettings();

        $this->form->fill([
            'blog_header_image' => $settings->blog_header_image,
            'gallery_parallax_bg' => $settings->gallery_parallax_bg,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Blog Header Image')
                    ->description('Gambar header untuk halaman blog')
                    ->schema([
                        FileUpload::make('blog_header_image')
                            ->label('Gambar Header Blog')
                            ->disk('public')
                            ->directory('blog/header')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '21:9',
                                '3:1',
                            ])
                            ->maxSize(2048)
                            ->helperText('Upload gambar untuk header halaman blog. Rekomendasi: 1920x600px. Max: 2MB')
                            ->columnSpanFull(),
                    ]),

                Section::make('Gallery Parallax Background')
                    ->description('Upload gambar background dengan efek parallax untuk halaman Gallery')
                    ->schema([
                        FileUpload::make('gallery_parallax_bg')
                            ->label('Background Parallax Gallery')
                            ->disk('public')
                            ->directory('backgrounds')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '21:9',
                            ])
                            ->maxSize(3072)
                            ->helperText('Gambar background untuk efek parallax di halaman gallery. Rekomendasi: 1920x1080px atau lebih besar. Max: 3MB')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = FooterSetting::first();

        if ($settings) {
            $settings->update($data);
        } else {
            FooterSetting::create($data);
        }

        Notification::make()
            ->title('Images Header berhasil disimpan')
            ->success()
            ->send();
    }
}
