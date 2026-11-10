<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PageStatus;
use App\Models\Site;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')
                ->label('سایت')
                ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            TextInput::make('title')
                ->label('عنوان')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    if (! filled($get('slug'))) {
                        $set('slug', Str::slug($state));
                    }
                }),
            TextInput::make('slug')->label('اسلاگ')->required(),
            Select::make('status')
                ->label('وضعیت')
                ->options(collect(PageStatus::cases())->mapWithKeys(
                    fn (PageStatus $s) => [$s->value => $s->label()]
                ))
                ->required()
                ->default(PageStatus::Draft->value),
            DateTimePicker::make('published_at')->label('زمان انتشار')->nullable(),
            RichEditor::make('body')->label('محتوا')->columnSpanFull(),
        ]);
    }
}
