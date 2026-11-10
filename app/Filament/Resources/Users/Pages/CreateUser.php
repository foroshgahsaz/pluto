<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\Access\SyncUserSiteRoles;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var list<array{site_id?:int|string, role?:string, is_primary?:bool}> */
    protected array $pendingSiteRoles = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingSiteRoles = $data['site_roles'] ?? [];
        unset($data['site_roles']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! (auth()->user()?->can('users.assign_roles') || auth()->user()?->isGlobalOperator())) {
            return;
        }

        if ($this->pendingSiteRoles === []) {
            return;
        }

        app(SyncUserSiteRoles::class)->handle(auth()->user(), $this->record->fresh(), $this->pendingSiteRoles);
    }
}
