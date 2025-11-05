<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Pesanan';

    protected static ?string $modelLabel = 'Pesanan';

    protected static ?string $pluralModelLabel = 'Pesanan';

    protected static ?string $navigationGroup = 'Petshop';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Order')
                    ->schema([
                        Forms\Components\Placeholder::make('order_number')
                            ->label('Nomor Order')
                            ->content(fn (?Order $record) => $record?->order_number ?? '-'),

                        Forms\Components\Placeholder::make('user')
                            ->label('Pengguna')
                            ->content(fn (?Order $record) => $record?->user?->name ?? '-'),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('Tanggal Pemesanan')
                            ->content(fn (?Order $record) => optional($record?->created_at)?->translatedFormat('d F Y H:i') ?? '-'),
                    ])
                    ->columns(3)
                    ->hiddenOn('create'),

                Forms\Components\Section::make('Status & Pembayaran')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Order')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Sudah Dibayar',
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                'refunded' => 'Refund',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'unpaid' => 'Belum Dibayar',
                                'paid' => 'Sudah Dibayar',
                                'failed' => 'Gagal',
                                'refunded' => 'Refund',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Nomor Resi')
                            ->maxLength(255)
                            ->placeholder('Masukkan nomor resi jika sudah dikirim'),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Dibayar Pada')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('shipped_at')
                            ->label('Dikirim Pada')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Selesai Pada')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->label('Dibatalkan Pada')
                            ->seconds(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pelanggan')
                    ->schema([
                        Forms\Components\Placeholder::make('customer_name')
                            ->label('Nama')
                            ->content(fn (?Order $record) => $record?->customer_name ?? '-'),

                        Forms\Components\Placeholder::make('customer_email')
                            ->label('Email')
                            ->content(fn (?Order $record) => $record?->customer_email ?? '-'),

                        Forms\Components\Placeholder::make('customer_phone')
                            ->label('Nomor Telepon')
                            ->content(fn (?Order $record) => $record?->customer_phone ?? '-'),

                        Forms\Components\Placeholder::make('shipping_address')
                            ->label('Alamat Pengiriman')
                            ->content(fn (?Order $record) => $record?->shipping_address ?? '-')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('shipping_city')
                            ->label('Kota')
                            ->content(fn (?Order $record) => $record?->shipping_city ?? '-'),

                        Forms\Components\Placeholder::make('shipping_province')
                            ->label('Provinsi')
                            ->content(fn (?Order $record) => $record?->shipping_province ?? '-'),

                        Forms\Components\Placeholder::make('shipping_postal_code')
                            ->label('Kode Pos')
                            ->content(fn (?Order $record) => $record?->shipping_postal_code ?? '-'),
                    ])
                    ->columns(2)
                    ->hiddenOn('create'),

                Forms\Components\Section::make('Ringkasan Pembayaran')
                    ->schema([
                        Forms\Components\Placeholder::make('subtotal')
                            ->label('Subtotal')
                            ->content(fn (?Order $record) => $record ? 'Rp ' . number_format($record->subtotal, 0, ',', '.') : '-'),

                        Forms\Components\Placeholder::make('shipping_cost')
                            ->label('Ongkir')
                            ->content(fn (?Order $record) => $record ? 'Rp ' . number_format($record->shipping_cost, 0, ',', '.') : '-'),

                        Forms\Components\Placeholder::make('total')
                            ->label('Total')
                            ->content(fn (?Order $record) => $record ? 'Rp ' . number_format($record->total, 0, ',', '.') : '-'),
                    ])
                    ->columns(3)
                    ->hiddenOn('create'),

                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Pelanggan')
                            ->rows(3)
                            ->disabled()
                            ->helperText('Catatan dari pelanggan saat checkout'),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Catatan Admin')
                            ->rows(3)
                            ->placeholder('Catatan internal atau informasi tambahan untuk tim'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Nomor Order')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'pending',
                        'success' => 'delivered',
                        'info' => 'processing',
                        'warning' => 'paid',
                        'primary' => 'shipped',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Pending',
                        'paid' => 'Sudah Dibayar',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'refunded' => 'Refund',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->colors([
                        'secondary' => 'unpaid',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'warning' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Sudah Dibayar',
                        'failed' => 'Gagal',
                        'refunded' => 'Refund',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Order')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Sudah Dibayar',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'refunded' => 'Refund',
                    ])
                    ->searchable(),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Sudah Dibayar',
                        'failed' => 'Gagal',
                        'refunded' => 'Refund',
                    ])
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
            PaymentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
