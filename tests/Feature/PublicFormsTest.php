<?php

namespace Tests\Feature;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_active_form_renders_on_site_route(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $form = Form::factory()->create([
            'site_id' => $site->id,
            'title' => 'تماس با ما',
            'slug' => 'contact',
            'is_active' => true,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $site->id,
            'name' => 'full_name',
            'label' => 'نام کامل',
            'type' => FormFieldType::Text,
        ]);

        $this->get('/tehran/forms/contact')
            ->assertOk()
            ->assertSee('تماس با ما', false)
            ->assertSee('نام کامل', false);
    }

    public function test_inactive_or_foreign_form_is_not_found(): void
    {
        $tehran = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $fars = Site::factory()->regional()->create(['slug' => 'fars', 'is_active' => true]);

        Form::factory()->create([
            'site_id' => $tehran->id,
            'slug' => 'hidden',
            'is_active' => false,
        ]);
        Form::factory()->create([
            'site_id' => $fars->id,
            'slug' => 'fars-contact',
            'is_active' => true,
            'title' => 'فرم فارس',
        ]);

        $this->get('/tehran/forms/hidden')->assertNotFound();
        $this->get('/tehran/forms/fars-contact')->assertNotFound();
    }

    public function test_public_submit_creates_submission_for_site(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $form = Form::factory()->create([
            'site_id' => $site->id,
            'slug' => 'contact',
            'success_message' => 'پیام شما ثبت شد',
            'is_active' => true,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $site->id,
            'name' => 'full_name',
            'label' => 'نام',
            'type' => FormFieldType::Text,
            'is_required' => true,
        ]);

        $this->post('/tehran/forms/contact', [
            'full_name' => 'سارا محمدی',
        ])
            ->assertOk()
            ->assertSee('پیام شما ثبت شد', false);

        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'site_id' => $site->id,
        ]);

        $submission = FormSubmission::query()->first();
        $this->assertSame('سارا محمدی', $submission->payload_json['full_name']);
    }

    public function test_public_submit_validation_errors_are_shown(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $form = Form::factory()->create([
            'site_id' => $site->id,
            'slug' => 'contact',
            'is_active' => true,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $site->id,
            'name' => 'full_name',
            'label' => 'نام',
            'type' => FormFieldType::Text,
            'is_required' => true,
        ]);

        $this->from('/tehran/forms/contact')
            ->post('/tehran/forms/contact', [])
            ->assertRedirect('/tehran/forms/contact')
            ->assertSessionHasErrors('full_name');

        $this->assertSame(0, FormSubmission::query()->count());
    }

    public function test_public_submit_accepts_file_upload(): void
    {
        $site = Site::factory()->regional()->create(['slug' => 'tehran', 'is_active' => true]);
        $form = Form::factory()->create([
            'site_id' => $site->id,
            'slug' => 'resume',
            'is_active' => true,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'site_id' => $site->id,
            'name' => 'photo',
            'label' => 'تصویر',
            'type' => FormFieldType::Image,
            'is_required' => true,
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg', 120, 120);

        $this->post('/tehran/forms/resume', [
            'photo' => $file,
        ])->assertOk();

        $submission = FormSubmission::query()->first();
        $this->assertNotNull($submission);
        $this->assertArrayHasKey('media_id', $submission->payload_json['photo']);
        $this->assertSame(1, $submission->files()->count());
    }
}

