<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Janji Temu';
    
    protected static ?string $modelLabel = 'Janji Temu';
    
    protected static ?string $pluralModelLabel = 'Janji Temu';
    
    protected static ?string $navigationGroup = 'Manajemen Booking';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Booking')
                    ->schema([
                        Forms\Components\TextInput::make('booking_code')
                            ->label('Kode Booking')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Pemilik')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required(),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->minLength(8),
                            ]),
                        
                        Forms\Components\Select::make('pet_id')
                            ->label('Hewan Peliharaan')
                            ->relationship('pet', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Hewan')
                                    ->required(),
                                Forms\Components\Select::make('species')
                                    ->label('Jenis')
                                    ->options([
                                        'dog' => 'Anjing',
                                        'cat' => 'Kucing',
                                        'bird' => 'Burung',
                                        'rabbit' => 'Kelinci',
                                        'hamster' => 'Hamster',
                                        'other' => 'Lainnya',
                                    ])
                                    ->required(),
                            ]),
                        
                        Forms\Components\Select::make('doctor_id')
                            ->label('Dokter')
                            ->relationship('doctor', 'name')
                            ->searchable()
                            ->required()
                            ->preload(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Jadwal')
                    ->schema([
                        Forms\Components\DatePicker::make('appointment_date')
                            ->label('Tanggal Kunjungan')
                            ->required()
                            ->native(false)
                            ->minDate(now())
                            ->displayFormat('d F Y'),
                        
                        Forms\Components\TimePicker::make('appointment_time')
                            ->label('Jam Kunjungan')
                            ->required()
                            ->seconds(false)
                            ->minutesStep(30),
                        
                        Forms\Components\TimePicker::make('end_time')
                            ->label('Estimasi Selesai')
                            ->seconds(false)
                            ->minutesStep(30),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Konfirmasi',
                                'confirmed' => 'Dikonfirmasi',
                                'in_progress' => 'Sedang Berlangsung',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                'no_show' => 'Tidak Hadir',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Detail Kunjungan')
                    ->schema([
                        Forms\Components\Textarea::make('complaint')
                            ->label('Keluhan Utama')
                            ->rows(3)
                            ->maxLength(500),
                        
                        Forms\Components\Textarea::make('diagnosis')
                            ->label('Diagnosis')
                            ->rows(3)
                            ->helperText('Diisi oleh dokter'),
                        
                        Forms\Components\Textarea::make('treatment')
                            ->label('Tindakan')
                            ->rows(3)
                            ->helperText('Diisi oleh dokter'),
                        
                        Forms\Components\Textarea::make('prescription')
                            ->label('Resep Obat')
                            ->rows(3),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(2),
                    ])
                    ->columns(1)
                    ->collapsible(),
                
                Forms\Components\Section::make('Biaya & Poin')
                    ->schema([
                        Forms\Components\TextInput::make('total_cost')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000),
                        
                        Forms\Components\TextInput::make('loyalty_points_earned')
                            ->label('Poin Loyalty')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('Kode Booking')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('semibold'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pet.name')
                    ->label('Hewan')
                    ->searchable()
                    ->description(fn (Appointment $record): string => $record->pet->species_label ?? ''),
                
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Dokter')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->description(fn (Appointment $record): string => $record->appointment_time ? $record->appointment_time->format('H:i') : ''),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => fn ($state) => in_array($state, ['cancelled', 'no_show']),
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'confirmed',
                        'heroicon-o-arrow-path' => 'in_progress',
                        'heroicon-o-check-badge' => 'completed',
                        'heroicon-o-x-circle' => fn ($state) => in_array($state, ['cancelled', 'no_show']),
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'in_progress' => 'Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'no_show' => 'Tidak Hadir',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('loyalty_points_earned')
                    ->label('Poin')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('appointment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Konfirmasi',
                        'confirmed' => 'Dikonfirmasi',
                        'in_progress' => 'Sedang Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'no_show' => 'Tidak Hadir',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->today()),
                
                Tables\Filters\Filter::make('upcoming')
                    ->label('Yang Akan Datang')
                    ->query(fn (Builder $query): Builder => $query->upcoming()),
                
                Tables\Filters\Filter::make('appointment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('appointment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('appointment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('confirm')
                        ->label('Konfirmasi')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Appointment $record): bool => $record->status === 'pending')
                        ->action(function (Appointment $record) {
                            $record->update(['status' => 'confirmed']);
                        }),
                    
                    Tables\Actions\Action::make('checkin')
                        ->label('Check-in')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->visible(fn (Appointment $record): bool => $record->status === 'confirmed')
                        ->action(function (Appointment $record) {
                            $record->update([
                                'status' => 'in_progress',
                                'checked_in_at' => now(),
                            ]);
                        }),
                    
                    Tables\Actions\Action::make('complete')
                        ->label('Selesai')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->form([
                            Forms\Components\Textarea::make('diagnosis')
                                ->label('Diagnosis')
                                ->required(),
                            Forms\Components\Textarea::make('treatment')
                                ->label('Tindakan'),
                            Forms\Components\Textarea::make('prescription')
                                ->label('Resep Obat'),
                            Forms\Components\TextInput::make('total_cost')
                                ->label('Total Biaya')
                                ->numeric()
                                ->prefix('Rp')
                                ->required(),
                            Forms\Components\TextInput::make('loyalty_points_earned')
                                ->label('Poin Loyalty')
                                ->numeric()
                                ->default(10)
                                ->required(),
                        ])
                        ->visible(fn (Appointment $record): bool => $record->status === 'in_progress')
                        ->action(function (Appointment $record, array $data) {
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                                'diagnosis' => $data['diagnosis'],
                                'treatment' => $data['treatment'] ?? null,
                                'prescription' => $data['prescription'] ?? null,
                                'total_cost' => $data['total_cost'],
                                'loyalty_points_earned' => $data['loyalty_points_earned'],
                            ]);
                            
                            // Add loyalty points
                            \App\Models\LoyaltyPoint::create([
                                'user_id' => $record->user_id,
                                'points' => $data['loyalty_points_earned'],
                                'type' => 'earned',
                                'source' => 'appointment',
                                'appointment_id' => $record->id,
                                'description' => 'Poin dari kunjungan ' . $record->booking_code,
                                'expires_at' => now()->addYear(),
                            ]);

                            // Send notification to user
                            \App\Models\Notification::create([
                                'user_id' => $record->user_id,
                                'type' => 'appointment_completed',
                                'title' => 'Janji Temu Selesai',
                                'message' => 'Selamat! Anda mendapatkan ' . $data['loyalty_points_earned'] . ' poin loyalitas dari kunjungan Anda.',
                                'data' => [
                                    'booking_code' => $record->booking_code,
                                    'points' => $data['loyalty_points_earned'],
                                    'appointment_id' => $record->id,
                                ],
                            ]);
                        }),
                    
                    Tables\Actions\Action::make('cancel')
                        ->label('Batalkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('cancellation_reason')
                                ->label('Alasan Pembatalan')
                                ->required(),
                        ])
                        ->visible(fn (Appointment $record): bool => in_array($record->status, ['pending', 'confirmed']))
                        ->action(function (Appointment $record, array $data) {
                            $record->update([
                                'status' => 'cancelled',
                                'cancelled_at' => now(),
                                'cancelled_by' => 'admin',
                                'cancellation_reason' => $data['cancellation_reason'],
                            ]);
                        }),
                    
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
