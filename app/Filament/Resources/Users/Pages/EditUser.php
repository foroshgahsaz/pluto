<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\Access\SyncUserSiteRoles;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /** @var list<array{site_id?:int|string, role?:string, is_primary?:bool}> */
    protected array $pendingSiteRoles = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['site_roles'] = app(SyncUserSiteRoles::class)->currentMap($this->record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingSiteRoles = $data['site_roles'] ?? [];
        unset($data['site_roles']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (! (auth()->user()?->can('users.assign_roles') || auth()->user()?->isGlobalOperator())) {
            return;
        }

        app(SyncUserSiteRoles::class)->handle(auth()->user(), $this->record->fresh(), $this->pendingSiteRoles);
    }
}
