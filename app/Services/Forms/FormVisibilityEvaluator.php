<?php

namespace App\Services\Forms;

use App\Models\FormField;
use Illuminate\Support\Collection;

class FormVisibilityEvaluator
{
    /**
     * @param  Collection<int, FormField>  $fields
     * @param  array<string, mixed>  $payload
     * @return Collection<int, FormField>
     */
    public function visibleFields(Collection $fields, array $payload): Collection
    {
        return $fields->filter(fn (FormField $field): bool => $this->isVisible($field, $payload))->values();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function isVisible(FormField $field, array $payload): bool
    {
        $rules = $field->visibility_rules_json;

        if (! is_array($rules) || $rules === []) {
            return true;
        }

        // Support either a single rule object or a list of AND rules.
        $ruleList = array_is_list($rules) ? $rules : [$rules];

        foreach ($ruleList as $rule) {
            if (! is_array($rule)) {
                continue;
            }

            $target = (string) ($rule['field'] ?? '');
            $operator = (string) ($rule['operator'] ?? 'equals');
            $expected = $rule['value'] ?? null;
            $actual = $payload[$target] ?? null;

            $ok = match ($operator) {
                'equals' => (string) $actual === (string) $expected,
                'not_equals' => (string) $actual !== (string) $expected,
                'filled' => filled($actual),
                'empty' => blank($actual),
                'in' => in_array($actual, (array) $expected, true),
                default => true,
            };

            if (! $ok) {
                return false;
            }
        }

        return true;
    }
}
