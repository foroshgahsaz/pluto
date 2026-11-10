<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Concerns\GuardsAuthorizedSiteId;
use App\Filament\Resources\Authors\AuthorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAuthor extends CreateRecord
{
    use GuardsAuthorizedSiteId;

    protected static string $resource = AuthorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardSiteId($data, 'articles.create');
    }
}
