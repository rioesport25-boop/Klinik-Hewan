<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyPointResource\Pages;
use App\Filament\Resources\LoyaltyPointResource\RelationManagers;
use App\Models\LoyaltyPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoyaltyPointResource extends Resource
{
    protected static ?string $model = LoyaltyPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    
    protected static ?string $navigationLabel = 'Poin Loyalty';
    
    protected static ?string $modelLabel = 'Poin Loyalty';
    
    protected static ?string $pluralModelLabel = 'Poin Loyalty';
    
    protected static ?string $navigationGroup = 'Manajemen Booking';
    
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipe Transaksi')
                            ->options([
                                'earned' => 'Diperoleh',
                                'redeemed' => 'Ditukar',
                                'expired' => 'Kadaluarsa',
                                'adjusted' => 'Penyesuaian',
                            ])
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('points')
                            ->label('Jumlah Poin')
                            ->required()
                            ->numeric()
                            ->helperText('Gunakan angka negatif untuk pengurangan poin')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Forms\Components\Section::make('Detail')
                    ->schema([
                        Forms\Components\TextInput::make('source')
                            ->label('Sumber')
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('appointment_id')
                            ->label('Appointment (Opsional)')
                            ->relationship('appointment', 'booking_code')
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Tanggal Kadaluarsa')
                            ->displayFormat('d/m/Y H:i')
                            ->helperText('Kosongkan jika poin tidak kadaluarsa')
                            ->columnSpanFull(),
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
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'earned',
                        'danger' => 'redeemed',
                        'warning' => 'expired',
                        'info' => 'adjusted',
                    ])
                    ->icons([
                        'heroicon-o-arrow-up' => 'earned',
                        'heroicon-o-arrow-down' => 'redeemed',
                        'heroicon-o-clock' => 'expired',
                        'heroicon-o-wrench' => 'adjusted',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'earned' => 'Diperoleh',
                        'redeemed' => 'Ditukar',
                        'expired' => 'Kadaluarsa',
                        'adjusted' => 'Penyesuaian',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('points')
                    ->label('Poin')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? '+' . $state : (string) $state)
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->weight('semibold'),
                
                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('appointment.booking_code')
                    ->label('Booking')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Kadaluarsa')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Transaksi')
                    ->options([
                        'earned' => 'Diperoleh',
                        'redeemed' => 'Ditukar',
                        'expired' => 'Kadaluarsa',
                        'adjusted' => 'Penyesuaian',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('active')
                    ->label('Poin Aktif')
                    ->query(fn (Builder $query): Builder => $query->active()),
                
                Tables\Filters\Filter::make('expired')
                    ->label('Sudah Kadaluarsa')
                    ->query(fn (Builder $query): Builder => $query->expired()),
                
                Tables\Filters\Filter::make('expires_soon')
                    ->label('Akan Kadaluarsa (30 Hari)')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('expires_at', '>', now())
                              ->where('expires_at', '<=', now()->addDays(30))
                    ),
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
            'index' => Pages\ListLoyaltyPoints::route('/'),
            'create' => Pages\CreateLoyaltyPoint::route('/create'),
            'edit' => Pages\EditLoyaltyPoint::route('/{record}/edit'),
        ];
    }
}
