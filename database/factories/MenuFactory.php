<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => 'منوی اصلی',
            'location' => 'primary',
        ];
    }
}
