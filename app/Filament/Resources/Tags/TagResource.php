<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Tags\Schemas\TagForm;
use App\Filament\Resources\Tags\Tables\TagsTable;
use App\Models\Tag;
use App\Models\User;
use App\Support\Tenancy\SiteScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationLabel = 'برچسب‌ها';

    protected static ?string $modelLabel = 'برچسب';

    protected static ?string $pluralModelLabel = 'برچسب‌ها';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static string|\UnitEnum|null $navigationGroup = 'تحریریه';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
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
        return auth()->user()?->can('viewAny', Tag::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', Tag::class) ?? false;
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
