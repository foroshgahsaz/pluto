<?php

namespace App\Filament\Resources\HomepageSections;

use App\Filament\Resources\HomepageSections\Pages\CreateHomepageSection;
use App\Filament\Resources\HomepageSections\Pages\EditHomepageSection;
use App\Filament\Resources\HomepageSections\Pages\ListHomepageSections;
use App\Filament\Resources\HomepageSections\Schemas\HomepageSectionForm;
use App\Filament\Resources\HomepageSections\Tables\HomepageSectionsTable;
use App\Models\HomepageSection;
use App\Models\User;
use App\Support\Tenancy\SiteScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomepageSectionResource extends Resource
{
    protected static ?string $model = HomepageSection::class;

    protected static ?string $navigationLabel = 'بخش‌های صفحه اصلی';

    protected static ?string $modelLabel = 'بخش';

    protected static ?string $pluralModelLabel = 'بخش‌های صفحه اصلی';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'ظاهر سایت';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return HomepageSectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HomepageSectionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomepageSections::route('/'),
            'create' => CreateHomepageSection::route('/create'),
            'edit' => EditHomepageSection::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SiteScope::class]);
        /** @var User|null $user */
        $user = auth()->user();
        if ($user && ! $user->isSuperAdmin()) {
            $query->whereIn('site_id', $user->authorizedSiteIds());
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', HomepageSection::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', HomepageSection::class) ?? false;
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
