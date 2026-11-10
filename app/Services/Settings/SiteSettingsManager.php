<?php

namespace App\Services\Settings;

use App\Models\Site;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;

class SiteSettingsManager
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function allGrouped(Site $site): array
    {
        return SiteSetting::query()
            ->where('site_id', $site->id)
            ->get()
            ->groupBy('group')
            ->map(fn ($rows) => $rows->mapWithKeys(fn (SiteSetting $row) => [$row->key => $row->value])->all())
            ->all();
    }

    public function get(Site $site, string $group, string $key, mixed $default = null): mixed
    {
        $row = SiteSetting::query()
            ->where('site_id', $site->id)
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        return $row?->value ?? $default;
    }

    public function set(Site $site, string $group, string $key, mixed $value): SiteSetting
    {
        $row = SiteSetting::query()->updateOrCreate(
            [
                'site_id' => $site->id,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => is_array($value) ? $value : ['value' => $value],
            ]
        );

        activity('settings')
            ->performedOn($site)
            ->withProperties([
                'site_id' => $site->id,
                'group' => $group,
                'key' => $key,
            ])
            ->log('settings.updated');

        return $row;
    }

    /**
     * @param  array<string, mixed>  $pairs  key => value within a group
     */
    public function syncGroup(Site $site, string $group, array $pairs): void
    {
        DB::transaction(function () use ($site, $group, $pairs): void {
            foreach ($pairs as $key => $value) {
                $this->set($site, $group, (string) $key, $value);
            }
        });
    }
}
