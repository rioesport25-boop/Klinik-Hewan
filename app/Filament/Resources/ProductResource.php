<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\ProductImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ProductVariantsRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk';

    protected static ?string $navigationGroup = 'Petshop';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create'
                                ? $set('slug', \Illuminate\Support\Str::slug($state))
                                : null)
                            ->placeholder('contoh: Makanan Kucing Premium'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('makanan-kucing-premium'),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(100)
                            ->helperText('Opsional, digunakan untuk pencatatan stok'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Deskripsi & Spesifikasi')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->placeholder('Deskripsi singkat produk'),

                        Forms\Components\RichEditor::make('specifications')
                            ->label('Spesifikasi')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'blockquote',
                            ])
                            ->columnSpanFull()
                            ->placeholder('Detail spesifikasi, komposisi, atau informasi tambahan produk'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Harga & Inventori')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('compare_price')
                            ->label('Harga Coret')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->helperText('Opsional. Tampilkan harga sebelum diskon'),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Total stok produk atau gabungan varian'),

                        Forms\Components\TextInput::make('weight')
                            ->label('Berat (gram)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Digunakan untuk perhitungan ongkir'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Produk tidak aktif tidak akan tampil di halaman pengguna'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Produk Unggulan')
                            ->default(false)
                            ->helperText('Tandai sebagai produk unggulan untuk ditampilkan di highlight'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Statistik')
                    ->schema([
                        Forms\Components\Placeholder::make('view_count')
                            ->label('Dilihat')
                            ->content(fn (?Product $record) => $record?->view_count ?? 0),

                        Forms\Components\Placeholder::make('order_count')
                            ->label('Terjual')
                            ->content(fn (?Product $record) => $record?->order_count ?? 0),

                        Forms\Components\Placeholder::make('rating_average')
                            ->label('Rating')
                            ->content(fn (?Product $record) => number_format($record?->rating_average ?? 0, 1)),

                        Forms\Components\Placeholder::make('review_count')
                            ->label('Jumlah Review')
                            ->content(fn (?Product $record) => $record?->review_count ?? 0),
                    ])
                    ->columns(4)
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image')
                    ->label('Gambar')
                    ->disk('public')
                    ->square()
                    ->getStateUsing(fn (Product $record) => optional($record->primary_image)->image_path)
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=Produk&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->trueColor('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Produk')
                    ->trueLabel('Hanya Aktif')
                    ->falseLabel('Hanya Tidak Aktif'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Produk Unggulan')
                    ->placeholder('Semua')
                    ->trueLabel('Hanya Unggulan')
                    ->falseLabel('Hanya Biasa'),
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ProductImagesRelationManager::class,
            ProductVariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
