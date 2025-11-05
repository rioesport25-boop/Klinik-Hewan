<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Layanan';

    protected static ?string $modelLabel = 'Layanan';

    protected static ?string $pluralModelLabel = 'Layanan';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Layanan')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Layanan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh: Pemeriksaan Umum'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Deskripsi singkat tentang layanan'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Icon & Tampilan')
                    ->schema([
                        Forms\Components\FileUpload::make('icon_path')
                            ->label('Icon Layanan')
                            ->disk('public')
                            ->directory('services/icons')
                            ->visibility('public')
                            ->image()
                            ->maxSize(512)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                            ->helperText('Upload icon untuk layanan (opsional). Rekomendasi: 128x128px, format PNG/JPG. Max: 512KB'),

                        Forms\Components\Select::make('icon_color')
                            ->label('Warna Background Icon')
                            ->options([
                                'bg-amber-600' => 'Amber (Default)',
                                'bg-blue-600' => 'Blue',
                                'bg-green-600' => 'Green',
                                'bg-red-600' => 'Red',
                                'bg-purple-600' => 'Purple',
                                'bg-pink-600' => 'Pink',
                                'bg-indigo-600' => 'Indigo',
                                'bg-teal-600' => 'Teal',
                                'bg-orange-600' => 'Orange',
                                'bg-cyan-600' => 'Cyan',
                            ])
                            ->default('bg-amber-600')
                            ->required()
                            ->helperText('Pilih warna background untuk icon'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('Angka untuk mengurutkan tampilan layanan (semakin kecil semakin awal)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Hanya layanan aktif yang akan ditampilkan di website'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon_path')
                    ->label('Icon')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=Service&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\ColorColumn::make('icon_color')
                    ->label('Warna')
                    ->copyable()
                    ->copyMessage('Warna disalin'),

                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Layanan')
                    ->trueLabel('Hanya Aktif')
                    ->falseLabel('Hanya Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
