<?php

namespace App\Services\Forms;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\User;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubmitFormService
{
    public function __construct(
        protected FormVisibilityEvaluator $visibility,
        protected BuildFormValidationRules $rulesBuilder,
        protected MediaUploadService $mediaUpload,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(Form $form, array $payload, ?User $actor = null, ?string $ip = null, ?string $userAgent = null): FormSubmission
    {
        if (! $form->is_active) {
            throw ValidationException::withMessages([
                'form' => 'این فرم غیرفعال است.',
            ]);
        }

        $fields = $form->fields()->get();
        $visible = $this->visibility->visibleFields($fields, $payload);
        $rules = $this->rulesBuilder->handle($fields, $payload);

        $validator = Validator::make($payload, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        // Drop values for fields that are not visible (conditional visibility must not bypass server rules).
        $visibleNames = $visible->pluck('name')->all();
        $validated = collect($validated)->only($visibleNames)->all();

        return DB::transaction(function () use ($form, $visible, $validated, $actor, $ip, $userAgent): FormSubmission {
            $payloadForStorage = $validated;
            $uploadMap = [];

            foreach ($visible as $field) {
                /** @var FormField $field */
                if (! $field->type->isUpload()) {
                    continue;
                }

                $value = $validated[$field->name] ?? null;
                if (! $value instanceof UploadedFile) {
                    unset($payloadForStorage[$field->name]);

                    continue;
                }

                $media = $this->mediaUpload->store($value, [
                    'site_id' => $form->site_id,
                    'visibility' => 'private',
                    'category' => $field->type === FormFieldType::Image ? 'image' : 'attachment',
                ], $actor);

                $uploadMap[$field->id] = $media->id;
                $payloadForStorage[$field->name] = [
                    'media_id' => $media->id,
                    'original_name' => $media->original_name,
                ];
            }

            $submission = FormSubmission::query()->create([
                'form_id' => $form->id,
                'site_id' => $form->site_id,
                'user_id' => $actor?->id,
                'payload_json' => $payloadForStorage,
                'ip' => $ip,
                'user_agent' => $userAgent ? substr($userAgent, 0, 512) : null,
                'created_at' => now(),
            ]);

            foreach ($uploadMap as $fieldId => $mediaId) {
                $submission->files()->create([
                    'site_id' => $form->site_id,
                    'form_field_id' => $fieldId,
                    'media_id' => $mediaId,
                ]);
            }

            activity('forms')
                ->causedBy($actor)
                ->performedOn($submission)
                ->withProperties(['site_id' => $form->site_id, 'form_id' => $form->id])
                ->log('form.submitted');

            return $submission->load('files');
        });
    }
}
