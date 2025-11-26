<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    
    protected static ?string $navigationLabel = 'Ulasan';
    
    protected static ?string $modelLabel = 'Ulasan';
    
    protected static ?string $pluralModelLabel = 'Ulasan';
    
    protected static ?string $navigationGroup = 'Manajemen Booking';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Review')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('appointment_id')
                            ->label('Appointment')
                            ->relationship('appointment', 'booking_code')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('doctor_id')
                            ->label('Dokter')
                            ->relationship('doctor', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Forms\Components\Section::make('Penilaian')
                    ->schema([
                        Forms\Components\TextInput::make('rating')
                            ->label('Rating Keseluruhan')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(1)
                            ->suffix('/ 5')
                            ->columnSpan(2),
                        
                        Forms\Components\Select::make('service_quality')
                            ->label('Kualitas Layanan')
                            ->options([
                                'poor' => 'Buruk',
                                'fair' => 'Cukup',
                                'good' => 'Baik',
                                'excellent' => 'Sangat Baik',
                            ])
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('cleanliness')
                            ->label('Kebersihan')
                            ->options([
                                'poor' => 'Buruk',
                                'fair' => 'Cukup',
                                'good' => 'Baik',
                                'excellent' => 'Sangat Baik',
                            ])
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('friendliness')
                            ->label('Keramahan')
                            ->options([
                                'poor' => 'Buruk',
                                'fair' => 'Cukup',
                                'good' => 'Baik',
                                'excellent' => 'Sangat Baik',
                            ])
                            ->columnSpan(2),
                        
                        Forms\Components\Textarea::make('comment')
                            ->label('Komentar')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Tampilkan di Website')
                            ->default(true)
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Waktu Verifikasi')
                            ->displayFormat('d/m/Y H:i')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Dokter')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('appointment.booking_code')
                    ->label('Booking')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state))
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('service_quality')
                    ->label('Layanan')
                    ->colors([
                        'danger' => 'poor',
                        'warning' => 'fair',
                        'success' => 'good',
                        'primary' => 'excellent',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'poor' => 'Buruk',
                        'fair' => 'Cukup',
                        'good' => 'Baik',
                        'excellent' => 'Sangat Baik',
                        default => '-',
                    })
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Ditampilkan')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('verified_at')
                    ->label('Terverifikasi')
                    ->boolean()
                    ->getStateUsing(fn (Review $record): bool => $record->verified_at !== null)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('Rating')
                    ->options([
                        5 => '5 Bintang ⭐⭐⭐⭐⭐',
                        4 => '4 Bintang ⭐⭐⭐⭐',
                        3 => '3 Bintang ⭐⭐⭐',
                        2 => '2 Bintang ⭐⭐',
                        1 => '1 Bintang ⭐',
                    ]),
                
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Ditampilkan')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),
                
                Tables\Filters\TernaryFilter::make('verified')
                    ->label('Terverifikasi')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Belum')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('verified_at'),
                        false: fn (Builder $query) => $query->whereNull('verified_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('verify')
                        ->label('Verifikasi')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Review $record): bool => $record->verified_at === null)
                        ->action(function (Review $record) {
                            $record->update(['verified_at' => now()]);
                        }),
                    
                    Tables\Actions\Action::make('toggleVisibility')
                        ->label(fn (Review $record): string => $record->is_visible ? 'Sembunyikan' : 'Tampilkan')
                        ->icon(fn (Review $record): string => $record->is_visible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Review $record) {
                            $record->update(['is_visible' => !$record->is_visible]);
                        }),
                    
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify')
                        ->label('Verifikasi Terpilih')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn (Review $record) => $record->update(['verified_at' => now()]));
                        }),
                    
                    Tables\Actions\BulkAction::make('show')
                        ->label('Tampilkan Terpilih')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn (Review $record) => $record->update(['is_visible' => true]));
                        }),
                    
                    Tables\Actions\BulkAction::make('hide')
                        ->label('Sembunyikan Terpilih')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn (Review $record) => $record->update(['is_visible' => false]));
                        }),
                    
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
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
