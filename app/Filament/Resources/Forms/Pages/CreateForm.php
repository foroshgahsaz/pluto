<?php

namespace App\Filament\Resources\Forms\Pages;

use App\Filament\Concerns\GuardsAuthorizedSiteId;
use App\Filament\Resources\Forms\FormResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForm extends CreateRecord
{
    use GuardsAuthorizedSiteId;

    protected static string $resource = FormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardSiteId($data, 'forms.create');
    }
}
