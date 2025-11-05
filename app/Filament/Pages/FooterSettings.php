<?php

namespace App\Filament\Pages;

use App\Models\FooterSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class FooterSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    protected static string $view = 'filament.pages.footer-settings';

    protected static ?string $navigationLabel = 'Footer Settings';

    protected static ?string $navigationGroup = 'Website Settings';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = FooterSetting::getSettings();

        $this->form->fill([
            'about_text' => $settings->about_text,
            'contact_phone' => $settings->contact_phone,
            'contact_email' => $settings->contact_email,
            'contact_address' => $settings->contact_address,
            'google_maps_iframe' => $settings->google_maps_iframe,
            'instagram_url' => $settings->instagram_url,
            'facebook_url' => $settings->facebook_url,
            'tiktok_url' => $settings->tiktok_url,
            'youtube_url' => $settings->youtube_url,
            'blog_header_image' => $settings->blog_header_image,
            'whatsapp_number' => $settings->whatsapp_number,
            'logo' => $settings->logo,
            'logo_dark' => $settings->logo_dark,
            'gallery_parallax_bg' => $settings->gallery_parallax_bg,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Logo Website')
                    ->description('Upload logo untuk mode terang dan gelap')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo (Mode Terang)')
                            ->disk('public')
                            ->directory('logos')
                            ->image()
                            ->imageEditor()
                            ->maxSize(1024)
                            ->helperText('Logo yang akan ditampilkan di mode terang. Format: PNG, JPG, SVG. Max: 1MB'),
                        FileUpload::make('logo_dark')
                            ->label('Logo (Mode Gelap)')
                            ->disk('public')
                            ->directory('logos')
                            ->image()
                            ->imageEditor()
                            ->maxSize(1024)
                            ->helperText('Logo yang akan ditampilkan di mode gelap. Format: PNG, JPG, SVG. Max: 1MB'),
                    ])
                    ->columns(2),

                Section::make('Gallery Parallax Background')
                    ->description('Upload gambar background dengan efek parallax untuk halaman Gallery')
                    ->schema([
                        FileUpload::make('gallery_parallax_bg')
                            ->label('Background Parallax')
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

                Section::make('About')
                    ->description('Informasi tentang klinik hewan')
                    ->schema([
                        Textarea::make('about_text')
                            ->label('Tentang Kami')
                            ->rows(4)
                            ->maxLength(500)
                            ->placeholder('Masukkan deskripsi singkat tentang klinik hewan...')
                            ->helperText('Teks yang akan ditampilkan di bagian About di footer'),
                    ]),

                Section::make('Contact Information')
                    ->description('Informasi kontak klinik')
                    ->schema([
                        TextInput::make('contact_phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->placeholder('+62 812-3456-7890')
                            ->helperText('Format: +62 atau 08xx'),

                        TextInput::make('contact_email')
                            ->label('Email')
                            ->email()
                            ->placeholder('info@klinikhewan.com'),

                        Textarea::make('contact_address')
                            ->label('Alamat')
                            ->rows(3)
                            ->placeholder('Jl. Contoh No. 123, Kota, Provinsi 12345'),

                        Textarea::make('google_maps_iframe')
                            ->label('Google Maps Embed Code')
                            ->rows(5)
                            ->placeholder('<iframe src="https://www.google.com/maps/embed?pb=..." width="300" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>')
                            ->helperText('Paste kode iframe dari Google Maps. Cara: Buka Google Maps > Pilih lokasi > Klik Share > Embed a map > Copy HTML code')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Social Media Links')
                    ->description('Link ke akun social media klinik')
                    ->schema([
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->placeholder('https://instagram.com/username')
                            ->helperText('Link lengkap ke profil Instagram'),

                        TextInput::make('facebook_url')
                            ->label('Facebook')
                            ->url()
                            ->placeholder('https://facebook.com/pagename')
                            ->helperText('Link lengkap ke halaman Facebook'),

                        TextInput::make('tiktok_url')
                            ->label('TikTok')
                            ->url()
                            ->placeholder('https://tiktok.com/@username')
                            ->helperText('Link lengkap ke profil TikTok'),

                        TextInput::make('youtube_url')
                            ->label('YouTube')
                            ->url()
                            ->placeholder('https://youtube.com/@channelname')
                            ->helperText('Link lengkap ke channel YouTube'),
                    ])
                    ->columns(2),

                Section::make('Blog Header Image')
                    ->description('Gambar header untuk halaman blog')
                    ->schema([
                        FileUpload::make('blog_header_image')
                            ->label('Gambar Header Blog')
                            ->disk('public')
                            ->directory('blog/header')
                            ->image()
                            ->maxSize(2048)
                            ->helperText('Upload gambar untuk header halaman blog. Rekomendasi: 1920x600px. Max: 2MB')
                            ->columnSpanFull(),
                    ]),

                Section::make('WhatsApp Floating Button')
                    ->description('Nomor WhatsApp untuk floating button')
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->placeholder('628123456789')
                            ->helperText('Format: 628xxx (tanpa tanda + atau spasi). Contoh: 628123456789')
                            ->maxLength(15)
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
            ->title('Footer settings berhasil disimpan')
            ->success()
            ->send();
    }
}
