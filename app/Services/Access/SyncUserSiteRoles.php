<?php

namespace App\Services\Access;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SyncUserSiteRoles
{
    public function __construct(
        protected AssignSiteRole $assigner,
    ) {}

    /**
     * @param  list<array{site_id:int|string, role:string, is_primary?:bool}>  $rows
     */
    public function handle(User $actor, User $target, array $rows): void
    {
        $normalized = collect($rows)
            ->filter(fn ($row) => filled($row['site_id'] ?? null) && filled($row['role'] ?? null))
            ->map(fn ($row) => [
                'site_id' => (int) $row['site_id'],
                'role' => (string) $row['role'],
                'is_primary' => (bool) ($row['is_primary'] ?? false),
            ])
            ->unique('site_id')
            ->values();

        DB::transaction(function () use ($actor, $target, $normalized): void {
            $previousTeam = getPermissionsTeamId();
            try {
                $keepSiteIds = $normalized->pluck('site_id')->all();
                $currentSiteIds = $target->sites()->pluck('sites.id')->map(fn ($id) => (int) $id)->all();

                foreach (array_diff($currentSiteIds, $keepSiteIds) as $detachId) {
                    setPermissionsTeamId($detachId);
                    $target->syncRoles([]);
                    $target->sites()->detach($detachId);
                }

                $primarySet = false;
                foreach ($normalized as $row) {
                    $this->assigner->handle($actor, $target, $row['site_id'], $row['role']);
                    $isPrimary = $row['is_primary'] && ! $primarySet;
                    if ($isPrimary) {
                        $primarySet = true;
                    }
                    $target->sites()->syncWithoutDetaching([
                        $row['site_id'] => ['is_primary' => $isPrimary],
                    ]);
                }

                if (! $primarySet && $keepSiteIds !== []) {
                    $target->sites()->updateExistingPivot($keepSiteIds[0], ['is_primary' => true]);
                }
            } finally {
                setPermissionsTeamId($previousTeam);
            }
        });
    }

    /**
     * @return list<array{site_id:int, role:string, is_primary:bool}>
     */
    public function currentMap(User $user): array
    {
        $rows = [];
        foreach ($user->sites()->orderBy('sites.name')->get() as $site) {
            $previous = getPermissionsTeamId();
            setPermissionsTeamId($site->id);
            try {
                $role = $user->fresh()->getRoleNames()->first() ?: 'journalist';
            } finally {
                setPermissionsTeamId($previous);
            }

            $rows[] = [
                'site_id' => (int) $site->id,
                'role' => (string) $role,
                'is_primary' => (bool) $site->pivot->is_primary,
            ];
        }

        return $rows;
    }
}
