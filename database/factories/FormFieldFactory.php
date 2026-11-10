<?php

namespace Database\Factories;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        $label = fake()->words(2, true);
        $name = Str::snake($label).'_'.fake()->unique()->numerify('##');

        return [
            'form_id' => Form::factory(),
            'site_id' => fn (array $attributes) => Form::query()->find($attributes['form_id'])?->site_id,
            'step' => 1,
            'type' => FormFieldType::Text,
            'name' => $name,
            'label' => $label,
            'help_text' => null,
            'placeholder' => null,
            'is_required' => false,
            'validation_rules_json' => null,
            'options_json' => null,
            'visibility_rules_json' => null,
            'sort_order' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (FormField $field): void {
            if (! $field->site_id && $field->form_id) {
                $field->site_id = Form::query()->find($field->form_id)?->site_id;
            }
        });
    }
}
