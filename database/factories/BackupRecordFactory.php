<?php

namespace Database\Factories;

use App\Models\BackupRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BackupRecord>
 */
class BackupRecordFactory extends Factory
{
    protected $model = BackupRecord::class;

    public function definition(): array
    {
        return [
            'status' => 'success',
            'type' => 'full',
            'disk' => 'local',
            'backup_name' => 'Pluto',
            'path' => 'Pluto/'.now()->format('Y-m-d-H-i-s').'.zip',
            'size_bytes' => fake()->numberBetween(1000, 5000000),
            'message' => null,
            'triggered_by' => null,
            'finished_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'failed',
            'path' => null,
            'size_bytes' => null,
            'message' => 'backup failed',
        ]);
    }

    public function database(): static
    {
        return $this->state(fn (): array => ['type' => 'database']);
    }

    public function files(): static
    {
        return $this->state(fn (): array => ['type' => 'files']);
    }

    public function triggeredBy(User $user): static
    {
        return $this->state(fn (): array => [
            'triggered_by' => $user->id,
        ]);
    }
}
