<?php

namespace Database\Factories;

use App\Enums\SiteType;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'parent_id' => null,
            'type' => SiteType::Regional,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'locale' => 'fa',
            'timezone' => 'Asia/Tehran',
            'is_active' => true,
        ];
    }

    public function central(): static
    {
        return $this->state(fn (): array => [
            'type' => SiteType::Central,
            'name' => 'سایت مرکزی',
            'slug' => 'central',
        ]);
    }

    public function regional(): static
    {
        return $this->state(fn (): array => [
            'type' => SiteType::Regional,
        ]);
    }
}
