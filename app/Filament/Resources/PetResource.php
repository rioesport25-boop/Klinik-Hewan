<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PetResource\Pages;
use App\Filament\Resources\PetResource\RelationManagers;
use App\Models\Pet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PetResource extends Resource
{
    protected static ?string $model = Pet::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    
    protected static ?string $navigationLabel = 'Hewan Peliharaan';
    
    protected static ?string $modelLabel = 'Hewan Peliharaan';
    
    protected static ?string $pluralModelLabel = 'Hewan Peliharaan';
    
    protected static ?string $navigationGroup = 'Manajemen Booking';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pemilik')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pemilik')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Nomor Telepon')
                                    ->tel(),
                            ]),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Informasi Hewan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Hewan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('species')
                            ->label('Jenis Hewan')
                            ->options([
                                'dog' => 'Anjing',
                                'cat' => 'Kucing',
                                'bird' => 'Burung',
                                'rabbit' => 'Kelinci',
                                'hamster' => 'Hamster',
                                'other' => 'Lainnya',
                            ])
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('breed')
                            ->label('Ras/Breed')
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Jantan',
                                'female' => 'Betina',
                            ])
                            ->columnSpan(1),
                        
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Tanggal Lahir')
                            ->maxDate(now())
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('weight')
                            ->label('Berat Badan (kg)')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('kg')
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('color')
                            ->label('Warna')
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->imageEditor()
                            ->directory('pets')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Forms\Components\Section::make('Informasi Kesehatan')
                    ->schema([
                        Forms\Components\Textarea::make('medical_history')
                            ->label('Riwayat Kesehatan')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('allergies')
                            ->label('Alergi')
                            ->placeholder('Tulis jika ada alergi terhadap makanan, obat, atau lainnya')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn (Pet $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('species')
                    ->label('Jenis')
                    ->colors([
                        'primary' => 'dog',
                        'success' => 'cat',
                        'warning' => 'bird',
                        'info' => 'rabbit',
                        'danger' => 'hamster',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dog' => 'Anjing',
                        'cat' => 'Kucing',
                        'bird' => 'Burung',
                        'rabbit' => 'Kelinci',
                        'hamster' => 'Hamster',
                        'other' => 'Lainnya',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('breed')
                    ->label('Ras')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'male' => 'Jantan',
                        'female' => 'Betina',
                        default => '-',
                    })
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('age_formatted')
                    ->label('Umur')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('weight')
                    ->label('Berat')
                    ->formatStateUsing(fn (?float $state): string => $state ? $state . ' kg' : '-')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('species')
                    ->label('Jenis Hewan')
                    ->options([
                        'dog' => 'Anjing',
                        'cat' => 'Kucing',
                        'bird' => 'Burung',
                        'rabbit' => 'Kelinci',
                        'hamster' => 'Hamster',
                        'other' => 'Lainnya',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Jantan',
                        'female' => 'Betina',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
                
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pemilik')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPets::route('/'),
            'create' => Pages\CreatePet::route('/create'),
            'edit' => Pages\EditPet::route('/{record}/edit'),
        ];
    }
}
