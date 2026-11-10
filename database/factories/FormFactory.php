<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'site_id' => Site::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->optional()->sentence(),
            'success_message' => 'فرم با موفقیت ثبت شد.',
            'is_active' => true,
            'is_multi_step' => false,
        ];
    }
}
