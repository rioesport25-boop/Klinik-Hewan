<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Filament\Resources\HolidayResource\RelationManagers;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Hari Libur';
    
    protected static ?string $modelLabel = 'Hari Libur';
    
    protected static ?string $pluralModelLabel = 'Hari Libur';
    
    protected static ?string $navigationGroup = 'Manajemen Jadwal';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Hari Libur')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Hari Libur')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh: Hari Raya Idul Fitri')
                            ->columnSpanFull(),
                        
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->minDate(now()->subYear())
                            ->maxDate(now()->addYears(2)),
                        
                        Forms\Components\Select::make('type')
                            ->label('Jenis Libur')
                            ->options([
                                'national' => 'Libur Nasional',
                                'religious' => 'Hari Keagamaan',
                                'custom' => 'Libur Khusus',
                            ])
                            ->required()
                            ->default('national'),
                        
                        Forms\Components\ColorPicker::make('color')
                            ->label('Warna Badge')
                            ->default('#ef4444')
                            ->required(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('Berulang Setiap Tahun')
                            ->helperText('Jika aktif, libur ini akan otomatis berlaku setiap tahun')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Hari Libur')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable()
                    ->description(fn (Holiday $record): string => $record->type_label),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'national' => 'success',
                        'religious' => 'warning',
                        'custom' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'national' => 'Nasional',
                        'religious' => 'Keagamaan',
                        'custom' => 'Khusus',
                    }),
                
                Tables\Columns\ColorColumn::make('color')
                    ->label('Warna'),
                
                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('Berulang')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-minus'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Libur')
                    ->options([
                        'national' => 'Libur Nasional',
                        'religious' => 'Hari Keagamaan',
                        'custom' => 'Libur Khusus',
                    ]),
                
                Tables\Filters\Filter::make('upcoming')
                    ->label('Yang Akan Datang')
                    ->query(fn (Builder $query): Builder => $query->upcoming()),
                
                Tables\Filters\Filter::make('this_year')
                    ->label('Tahun Ini')
                    ->query(fn (Builder $query): Builder => $query->inYear(now()->year)),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
