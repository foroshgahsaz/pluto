<?php

namespace App\Services\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Support\Tenancy\SiteScope;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportFormSubmissionsService
{
    public function toCsvResponse(Form $form): StreamedResponse
    {
        $fields = $form->fields()->orderBy('sort_order')->get();
        $headers = array_merge(['id', 'created_at', 'user_id'], $fields->pluck('name')->all());

        $filename = 'form-'.$form->id.'-submissions-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($form, $fields, $headers): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $headers);

            FormSubmission::query()
                ->withoutGlobalScopes([SiteScope::class])
                ->where('form_id', $form->id)
                ->where('site_id', $form->site_id)
                ->orderBy('id')
                ->chunkById(200, function (Collection $rows) use ($out, $fields): void {
                    foreach ($rows as $submission) {
                        /** @var FormSubmission $submission */
                        $payload = $submission->payload_json ?? [];
                        $line = [
                            $submission->id,
                            optional($submission->created_at)?->toDateTimeString(),
                            $submission->user_id,
                        ];

                        foreach ($fields as $field) {
                            $value = $payload[$field->name] ?? '';
                            if (is_array($value)) {
                                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                            }
                            $line[] = $value;
                        }

                        fputcsv($out, $line);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
