<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Filament\Resources\BlogPostResource\RelationManagers;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Blog Posts';

    protected static ?string $modelLabel = 'Blog Post';

    protected static ?string $pluralModelLabel = 'Blog Posts';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konten Artikel')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Artikel')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null)
                            ->placeholder('Masukkan judul artikel'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly version dari judul. Otomatis dibuat dari judul.')
                            ->placeholder('judul-artikel'),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Ringkasan singkat artikel (maks 500 karakter)')
                            ->placeholder('Tulis ringkasan artikel di sini...'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Konten Artikel')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Tulis konten lengkap artikel dengan formatting')
                            ->placeholder('Tulis konten artikel di sini...'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Gambar & Media')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->label('Gambar Unggulan')
                            ->disk('public')
                            ->directory('blog/images')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                            ])
                            ->maxSize(2048)
                            ->helperText('Upload gambar unggulan untuk artikel. Rekomendasi: 1200x675px (16:9). Max: 2MB'),
                    ]),

                Forms\Components\Section::make('Pengaturan Publikasi')
                    ->schema([
                        Forms\Components\TextInput::make('author')
                            ->label('Penulis')
                            ->required()
                            ->maxLength(255)
                            ->default('Admin')
                            ->placeholder('Nama penulis'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(false)
                            ->helperText('Aktifkan untuk mempublikasikan artikel')
                            ->live(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->default(now())
                            ->helperText('Tanggal dan waktu artikel dipublikasikan')
                            ->seconds(false),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Gambar')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=Blog&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('author')
                    ->label('Penulis')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->iconColor('primary'),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-o-eye')
                    ->iconColor('warning')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->placeholder('Semua Artikel')
                    ->trueLabel('Hanya Terpublikasi')
                    ->falseLabel('Hanya Draft'),
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
            ->defaultSort('published_at', 'desc');
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
