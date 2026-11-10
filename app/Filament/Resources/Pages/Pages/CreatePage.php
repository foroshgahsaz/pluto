<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\GuardsAuthorizedSiteId;
use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use GuardsAuthorizedSiteId;

    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardSiteId($data, 'pages.manage', sanitizeBody: true);
    }
}
