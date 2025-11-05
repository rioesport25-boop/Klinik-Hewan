<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'payment';

    protected static ?string $title = 'Pembayaran';

    public static function canViewForRecord($ownerRecord, $pageClass): bool
    {
        return true;
    }

    public function canCreate(): bool
    {
        return ! $this->getOwnerRecord()->payment()->exists();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tripay')
                    ->schema([
                        Forms\Components\TextInput::make('tripay_reference')
                            ->label('Tripay Reference')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('tripay_merchant_ref')
                            ->label('Merchant Reference')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Metode Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Metode')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('payment_channel')
                            ->label('Channel')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('payment_name')
                            ->label('Nama Metode')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Jumlah Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('fee')
                            ->label('Biaya Admin')
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Dibayar')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Status Pembayaran')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'expired' => 'Expired',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('payment_code')
                            ->label('Payment Code / VA')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('checkout_url')
                            ->label('Checkout URL')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Waktu')
                    ->schema([
                        Forms\Components\DateTimePicker::make('expired_at')
                            ->label('Expired At')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->seconds(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Tambahan')
                    ->schema([
                        Forms\Components\Textarea::make('qr_url')
                            ->label('QR URL')
                            ->rows(2),

                        Forms\Components\Textarea::make('tripay_response')
                            ->label('Tripay Response (JSON)')
                            ->rows(4)
                            ->helperText('Simpan raw response dari Tripay untuk referensi'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_channel')
                    ->label('Channel')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'warning' => 'expired',
                    ])
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('expired_at')
                    ->label('Expired')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Data Pembayaran')
                    ->hidden(fn () => $this->getOwnerRecord()->payment()->exists()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada data pembayaran')
            ->emptyStateDescription('Data pembayaran akan muncul setelah pelanggan melakukan checkout.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Data Pembayaran'),
            ]);
    }
}
