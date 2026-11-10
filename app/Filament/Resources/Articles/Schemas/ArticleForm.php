<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Author;
use App\Models\Category;
use App\Models\Site;
use App\Models\Tag;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('محتوا')
                    ->schema([
                        Select::make('site_id')
                            ->label('سایت')
                            ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->searchable(),
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get): void {
                                if (! $get('slug')) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('اسلاگ')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('summary')
                            ->label('خلاصه')
                            ->rows(3),
                        RichEditor::make('body')
                            ->label('متن خبر')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('طبقه‌بندی')
                    ->schema([
                        Select::make('category_id')
                            ->label('دسته‌بندی')
                            ->options(function (callable $get) {
                                $siteId = $get('site_id');

                                return Category::query()
                                    ->withoutGlobalScopes()
                                    ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->nullable(),
                        Select::make('author_id')
                            ->label('نویسنده')
                            ->options(function (callable $get) {
                                $siteId = $get('site_id');

                                return Author::query()
                                    ->withoutGlobalScopes()
                                    ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
                                    ->where('is_active', true)
                                    ->orderBy('display_name')
                                    ->pluck('display_name', 'id');
                            })
                            ->searchable()
                            ->nullable(),
                        Select::make('editor_id')
                            ->label('مدیر محتوا')
                            ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        Select::make('tags')
                            ->label('برچسب‌ها')
                            ->multiple()
                            ->relationship(
                                name: 'tags',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, callable $get) => $query
                                    ->withoutGlobalScopes()
                                    ->when($get('site_id'), fn ($q, $siteId) => $q->where('site_id', $siteId))
                            )
                            ->preload()
                            ->searchable(),
                        TagsInput::make('keyword_list')
                            ->label('کلمات کلیدی')
                            ->placeholder('کلیدواژه را وارد کنید')
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Section::make('انتشار و سئو')
                    ->schema([
                        Toggle::make('is_featured')
                            ->label('خبر ویژه'),
                        Toggle::make('is_selected')
                            ->label('خبر منتخب'),
                        DateTimePicker::make('scheduled_for')
                            ->label('زمان‌بندی انتشار')
                            ->seconds(false),
                        TextInput::make('meta_title')
                            ->label('عنوان سئو')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('توضیح سئو')
                            ->rows(2),
                        FileUpload::make('featured_image')
                            ->label('تصویر شاخص')
                            ->image()
                            ->directory('articles/featured')
                            ->disk('public')
                            ->visibility('public')
                            ->dehydrated(false),
                        Textarea::make('rejection_reason')
                            ->label('دلیل رد')
                            ->disabled()
                            ->visible(fn ($get) => filled($get('rejection_reason'))),
                    ])
                    ->columns(2),
            ]);
    }
}
