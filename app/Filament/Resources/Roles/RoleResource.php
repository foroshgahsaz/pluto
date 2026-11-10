<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationLabel = 'نقش‌ها و مجوزها';

    protected static ?string $modelLabel = 'نقش';

    protected static ?string $pluralModelLabel = 'نقش‌ها';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'امنیت و عملیات';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('نام سیستمی نقش')
                ->disabled()
                ->dehydrated(),
            CheckboxList::make('permissions')
                ->label('مجوزها')
                ->options(fn () => Permission::query()->orderBy('name')->pluck('name', 'name'))
                ->columns(2)
                ->bulkToggleable()
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نقش')->searchable(),
                TextColumn::make('permissions_count')->counts('permissions')->label('تعداد مجوز'),
                TextColumn::make('guard_name')->label('گارد')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Role $record): array {
                        $data['permissions'] = $record->permissions()->pluck('name')->all();

                        return $data;
                    })
                    ->using(function (Role $record, array $data): Role {
                        $permissions = $data['permissions'] ?? [];
                        $record->syncPermissions($permissions);

                        // Keep team-scoped copies of the same role name in sync with the template.
                        Role::query()
                            ->where('name', $record->name)
                            ->where('guard_name', $record->guard_name)
                            ->whereKeyNot($record->getKey())
                            ->each(fn (Role $role) => $role->syncPermissions($permissions));

                        app(PermissionRegistrar::class)->forgetCachedPermissions();

                        activity('access')
                            ->causedBy(auth()->user())
                            ->performedOn($record)
                            ->withProperties(['permissions' => $permissions])
                            ->log('role.permissions_synced');

                        return $record;
                    }),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Template roles only (team_id null). Site assignments reuse these names via teams.
        return parent::getEloquentQuery()
            ->whereNull(config('permission.column_names.team_foreign_key', 'team_id'));
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isSuperAdmin() || $user?->can('roles.view') || false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isSuperAdmin() || $user?->can('roles.update') || false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
