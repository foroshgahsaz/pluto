<?php

namespace Database\Factories;

use App\Enums\MenuItemType;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'site_id' => fn (array $attributes) => Menu::query()->find($attributes['menu_id'])?->site_id,
            'parent_id' => null,
            'title' => fake()->words(2, true),
            'type' => MenuItemType::Url,
            'reference_id' => null,
            'url' => fake()->url(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (MenuItem $item): void {
            if (! $item->site_id && $item->menu_id) {
                $item->site_id = Menu::query()->find($item->menu_id)?->site_id;
            }
        });
    }
}
