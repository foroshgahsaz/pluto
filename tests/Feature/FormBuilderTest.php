<?php

namespace Tests\Feature;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\Site;
use App\Models\User;
use App\Services\Access\AssignSiteRole;
use App\Services\Forms\ExportFormSubmissionsService;
use App\Services\Forms\SubmitFormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_submission_stores_payload_for_visible_required_fields(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $form->site_id,
            'name' => 'full_name',
            'label' => 'نام',
            'type' => FormFieldType::Text,
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $submission = app(SubmitFormService::class)->handle($form, [
            'full_name' => 'علی رضایی',
        ]);

        $this->assertSame('علی رضایی', $submission->payload_json['full_name']);
        $this->assertSame($form->site_id, $submission->site_id);
    }

    public function test_conditional_hidden_required_field_is_not_validated(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $form->site_id,
            'name' => 'has_company',
            'type' => FormFieldType::Select,
            'is_required' => true,
            'options_json' => [
                ['value' => 'yes', 'label' => 'بله'],
                ['value' => 'no', 'label' => 'خیر'],
            ],
            'sort_order' => 1,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $form->site_id,
            'name' => 'company_name',
            'type' => FormFieldType::Text,
            'is_required' => true,
            'visibility_rules_json' => [
                'field' => 'has_company',
                'operator' => 'equals',
                'value' => 'yes',
            ],
            'sort_order' => 2,
        ]);

        $submission = app(SubmitFormService::class)->handle($form, [
            'has_company' => 'no',
            'company_name' => 'should-be-ignored',
        ]);

        $this->assertSame('no', $submission->payload_json['has_company']);
        $this->assertArrayNotHasKey('company_name', $submission->payload_json);
    }

    public function test_conditional_visible_required_field_must_be_present(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $form->site_id,
            'name' => 'has_company',
            'type' => FormFieldType::Select,
            'is_required' => true,
            'options_json' => [
                ['value' => 'yes', 'label' => 'بله'],
                ['value' => 'no', 'label' => 'خیر'],
            ],
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $form->site_id,
            'name' => 'company_name',
            'type' => FormFieldType::Text,
            'is_required' => true,
            'visibility_rules_json' => [
                'field' => 'has_company',
                'operator' => 'equals',
                'value' => 'yes',
            ],
        ]);

        $this->expectException(ValidationException::class);
        app(SubmitFormService::class)->handle($form, [
            'has_company' => 'yes',
        ]);
    }

    public function test_tehran_user_cannot_view_fars_submissions(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran']);
        $fars = Site::factory()->regional()->create(['slug' => 'fars']);

        $tehranUser = User::factory()->create();
        app(AssignSiteRole::class)->handle(
            User::factory()->create(['is_super_admin' => true]),
            $tehranUser,
            $tehran->id,
            'regional_admin'
        );

        $farsForm = Form::factory()->create(['site_id' => $fars->id]);
        $submission = FormSubmission::factory()->create([
            'form_id' => $farsForm->id,
            'site_id' => $fars->id,
            'payload_json' => ['x' => 1],
        ]);

        $this->assertFalse($tehranUser->fresh()->can('view', $submission));
        $this->assertFalse($tehranUser->fresh()->can('viewSubmissions', $farsForm));
        $this->assertFalse($tehranUser->fresh()->can('exportSubmissions', $farsForm));
    }

    public function test_inactive_form_rejects_submission(): void
    {
        $form = Form::factory()->create(['is_active' => false]);

        $this->expectException(ValidationException::class);
        app(SubmitFormService::class)->handle($form, []);
    }

    public function test_file_field_creates_private_media_on_submit(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $form->site_id,
            'name' => 'resume',
            'type' => FormFieldType::File,
            'is_required' => true,
        ]);

        $path = sys_get_temp_dir().'/pluto-form-'.uniqid().'.pdf';
        file_put_contents($path, '%PDF-1.4 form upload');
        $file = new UploadedFile($path, 'resume.pdf', 'application/pdf', null, true);

        $submission = app(SubmitFormService::class)->handle($form, [
            'resume' => $file,
        ], User::factory()->create(['is_super_admin' => true]));

        $this->assertArrayHasKey('media_id', $submission->payload_json['resume']);
        $this->assertCount(1, $submission->files);
        $this->assertTrue($submission->files->first()->media->isPrivate());
        @unlink($path);
    }

    public function test_export_csv_streams_for_authorized_site(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $form->site_id,
            'name' => 'full_name',
            'type' => FormFieldType::Text,
        ]);
        FormSubmission::factory()->create([
            'form_id' => $form->id,
            'site_id' => $form->site_id,
            'payload_json' => ['full_name' => 'Test User'],
        ]);

        $response = app(ExportFormSubmissionsService::class)->toCsvResponse($form);
        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('full_name', $csv);
        $this->assertStringContainsString('Test User', $csv);
    }
}
