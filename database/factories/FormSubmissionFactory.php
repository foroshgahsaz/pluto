<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'site_id' => fn (array $attributes) => Form::query()->find($attributes['form_id'])?->site_id,
            'user_id' => null,
            'payload_json' => ['sample' => 'value'],
            'ip' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'created_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (FormSubmission $submission): void {
            if (! $submission->site_id && $submission->form_id) {
                $submission->site_id = Form::query()->find($submission->form_id)?->site_id;
            }
        });
    }
}
