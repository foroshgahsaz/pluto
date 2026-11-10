<?php

namespace App\Filament\Concerns;

use App\Support\Security\HtmlSanitizer;
use App\Support\Tenancy\AuthorizedSiteOptions;

trait GuardsAuthorizedSiteId
{
    protected function guardSiteId(array $data, string $permission, bool $sanitizeBody = false): array
    {
        $siteId = (int) ($data['site_id'] ?? 0);
        AuthorizedSiteOptions::assertWritable(auth()->user(), $siteId, $permission);

        if ($sanitizeBody && array_key_exists('body', $data)) {
            $data['body'] = app(HtmlSanitizer::class)->clean($data['body'] ?? '');
        }

        return $data;
    }
}
