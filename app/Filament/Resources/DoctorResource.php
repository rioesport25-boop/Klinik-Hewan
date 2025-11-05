<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Dokter';

    protected static ?string $modelLabel = 'Dokter';

    protected static ?string $pluralModelLabel = 'Dokter';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokter')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh: Ahmad Santoso'),

                        Forms\Components\TextInput::make('title')
                            ->label('Gelar')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh: drh., drh. M.Sc')
                            ->helperText('Gelar akademik dokter'),

                        Forms\Components\TextInput::make('specialization')
                            ->label('Spesialisasi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh: Spesialis Bedah'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Deskripsi singkat tentang dokter dan pengalaman'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Foto & Tampilan')
                    ->schema([
                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto Dokter')
                            ->image()
                            ->directory('doctors')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '3:4',
                                '1:1',
                            ])
                            ->maxSize(2048)
                            ->helperText('Upload foto dokter (opsional). Jika tidak diupload, akan menggunakan icon default dengan warna gradient.'),

                        Forms\Components\Select::make('gradient_color')
                            ->label('Warna Gradient (jika tidak ada foto)')
                            ->options([
                                'from-amber-400 to-orange-500' => 'Amber ke Orange',
                                'from-blue-400 to-indigo-500' => 'Blue ke Indigo',
                                'from-green-400 to-teal-500' => 'Green ke Teal',
                                'from-pink-400 to-rose-500' => 'Pink ke Rose',
                                'from-purple-400 to-violet-500' => 'Purple ke Violet',
                                'from-red-400 to-orange-500' => 'Red ke Orange',
                                'from-cyan-400 to-blue-500' => 'Cyan ke Blue',
                                'from-yellow-400 to-amber-500' => 'Yellow ke Amber',
                            ])
                            ->default('from-amber-400 to-orange-500')
                            ->required()
                            ->helperText('Pilih warna gradient untuk background jika tidak ada foto'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('Angka untuk mengurutkan tampilan dokter (semakin kecil semakin awal)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Hanya dokter aktif yang akan ditampilkan di website'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=Doctor&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Doctor $record): string => $record->title),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Spesialisasi')
                    ->searchable()
                    ->badge()
                    ->color('warning'),

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
                    ->placeholder('Semua Dokter')
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
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
