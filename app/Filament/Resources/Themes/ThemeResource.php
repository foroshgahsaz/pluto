<?php

namespace App\Filament\Resources\Themes;

use App\Filament\Resources\Themes\Pages\CreateTheme;
use App\Filament\Resources\Themes\Pages\EditTheme;
use App\Filament\Resources\Themes\Pages\ListThemes;
use App\Filament\Resources\Themes\Schemas\ThemeForm;
use App\Filament\Resources\Themes\Tables\ThemesTable;
use App\Models\Theme;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;

    protected static ?string $navigationLabel = 'قالب‌ها';

    protected static ?string $modelLabel = 'قالب';

    protected static ?string $pluralModelLabel = 'قالب‌ها';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|\UnitEnum|null $navigationGroup = 'ظاهر سایت';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ThemeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThemesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListThemes::route('/'),
            'create' => CreateTheme::route('/create'),
            'edit' => EditTheme::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', Theme::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', Theme::class) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('update', $record) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete', $record) ?? false;
    }
}
