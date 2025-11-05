<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Gambar Produk';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->label('Gambar')
                    ->disk('public')
                    ->directory('products/images')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->required()
                    ->maxSize(2048)
                    ->helperText('Upload gambar produk. Rekomendasi: 800x800px, max 2MB'),

                Forms\Components\TextInput::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Gambar dengan urutan terkecil akan ditampilkan lebih awal'),

                Forms\Components\Toggle::make('is_primary')
                    ->label('Set sebagai utama')
                    ->helperText('Gambar utama akan tampil sebagai thumbnail produk'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Utama')
                    ->boolean()
                    ->trueColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diupload')
                    ->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Gambar'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Belum ada gambar')
            ->emptyStateDescription('Tambah gambar produk untuk meningkatkan daya tarik pada halaman pengguna.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Gambar'),
            ]);
    }
}
