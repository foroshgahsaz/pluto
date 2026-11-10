<?php

namespace App\Filament\Resources\MediaFolders;

use App\Filament\Resources\MediaFolders\Pages\CreateMediaFolder;
use App\Filament\Resources\MediaFolders\Pages\EditMediaFolder;
use App\Filament\Resources\MediaFolders\Pages\ListMediaFolders;
use App\Filament\Resources\MediaFolders\Schemas\MediaFolderForm;
use App\Filament\Resources\MediaFolders\Tables\MediaFoldersTable;
use App\Models\MediaFolder;
use App\Models\User;
use App\Support\Tenancy\SiteScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MediaFolderResource extends Resource
{
    protected static ?string $model = MediaFolder::class;

    protected static ?string $navigationLabel = 'پوشه‌های رسانه';

    protected static ?string $modelLabel = 'پوشه';

    protected static ?string $pluralModelLabel = 'پوشه‌ها';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|\UnitEnum|null $navigationGroup = 'رسانه';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return MediaFolderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaFoldersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaFolders::route('/'),
            'create' => CreateMediaFolder::route('/create'),
            'edit' => EditMediaFolder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SiteScope::class]);
        /** @var User|null $user */
        $user = auth()->user();
        if ($user && ! $user->isGlobalOperator()) {
            $query->whereIn('site_id', $user->authorizedSiteIds());
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', MediaFolder::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', MediaFolder::class) ?? false;
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
