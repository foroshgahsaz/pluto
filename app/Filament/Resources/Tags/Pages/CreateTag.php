<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Concerns\GuardsAuthorizedSiteId;
use App\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    use GuardsAuthorizedSiteId;

    protected static string $resource = TagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardSiteId($data, 'articles.create');
    }
}
