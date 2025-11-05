<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Varian Produk';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Varian')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('contoh: Rasa Salmon - 1kg'),

                Forms\Components\TextInput::make('size')
                    ->label('Ukuran')
                    ->maxLength(100)
                    ->placeholder('contoh: 1kg'),

                Forms\Components\TextInput::make('color')
                    ->label('Warna')
                    ->maxLength(100)
                    ->placeholder('contoh: Merah'),

                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(100)
                    ->placeholder('Opsional'),

                Forms\Components\TextInput::make('price_adjustment')
                    ->label('Penyesuaian Harga')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('Rp')
                    ->default(0)
                    ->helperText('Gunakan angka positif (+) untuk menambah harga atau negatif (-) untuk mengurangi harga dasar'),

                Forms\Components\TextInput::make('stock')
                    ->label('Stok Varian')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('size')
                    ->label('Ukuran')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('color')
                    ->label('Warna')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('price_adjustment')
                    ->label('Penyesuaian Harga')
                    ->money('IDR', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Varian'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Belum ada varian')
            ->emptyStateDescription('Tambah varian produk untuk mengelola kombinasi ukuran atau warna.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Varian'),
            ]);
    }
}
