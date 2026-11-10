<?php

namespace App\Services\Forms;

use App\Enums\FormFieldType;
use App\Models\FormField;
use Illuminate\Support\Collection;

class BuildFormValidationRules
{
    public function __construct(
        protected FormVisibilityEvaluator $visibility,
    ) {}

    /**
     * @param  Collection<int, FormField>  $fields
     * @param  array<string, mixed>  $payload
     * @return array<string, list<string>>
     */
    public function handle(Collection $fields, array $payload): array
    {
        $rules = [];

        foreach ($this->visibility->visibleFields($fields, $payload) as $field) {
            $type = $field->type instanceof FormFieldType ? $field->type : FormFieldType::from((string) $field->type);
            $fieldRules = $type->defaultRules((bool) $field->is_required);

            $extra = $field->validation_rules_json;
            if (is_array($extra)) {
                foreach ($extra as $rule) {
                    if (is_string($rule) && $rule !== '') {
                        $fieldRules[] = $rule;
                    }
                }
            }

            if ($type === FormFieldType::Select || $type === FormFieldType::MultiSelect) {
                $options = collect($field->options_json ?? [])
                    ->map(fn ($option) => is_array($option) ? ($option['value'] ?? null) : $option)
                    ->filter()
                    ->values()
                    ->all();

                if ($options !== []) {
                    if ($type === FormFieldType::MultiSelect) {
                        $fieldRules[] = 'array';
                        $rules[$field->name.'.*'] = ['in:'.implode(',', $options)];
                    } else {
                        $fieldRules[] = 'in:'.implode(',', $options);
                    }
                }
            }

            $rules[$field->name] = array_values(array_unique($fieldRules));
        }

        return $rules;
    }
}
