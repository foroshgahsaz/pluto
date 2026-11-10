<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\GuardsAuthorizedSiteId;
use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use GuardsAuthorizedSiteId;

    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['site_id'] = $this->getRecord()->site_id;

        return $this->guardSiteId($data, 'pages.manage', sanitizeBody: true);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
