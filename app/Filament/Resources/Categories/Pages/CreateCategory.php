<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\GuardsAuthorizedSiteId;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use GuardsAuthorizedSiteId;

    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardSiteId($data, 'categories.create');
    }
}
