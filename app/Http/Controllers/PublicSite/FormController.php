<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Site;
use App\Services\Forms\SubmitFormService;
use App\Services\PublicSite\PublicSiteViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FormController extends Controller
{
    public function __construct(
        protected PublicSiteViewData $viewData,
        protected SubmitFormService $submitter,
    ) {}

    public function show(Site $site, string $slug): View
    {
        $form = $this->findActiveForm($site, $slug);

        return view('public.forms.show', [
            'site' => $site,
            'branding' => $this->viewData->branding($site),
            'menu' => $this->viewData->primaryMenu($site),
            'form' => $form->load('fields'),
            'submitted' => false,
        ]);
    }

    public function submit(Request $request, Site $site, string $slug): RedirectResponse|View
    {
        $form = $this->findActiveForm($site, $slug);

        try {
            $this->submitter->handle(
                $form,
                $request->all(),
                $request->user(),
                $request->ip(),
                $request->userAgent(),
            );
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors())
                ->withInput($request->except(array_keys($request->allFiles())));
        }

        return view('public.forms.show', [
            'site' => $site,
            'branding' => $this->viewData->branding($site),
            'menu' => $this->viewData->primaryMenu($site),
            'form' => $form->load('fields'),
            'submitted' => true,
        ]);
    }

    protected function findActiveForm(Site $site, string $slug): Form
    {
        $form = Form::query()
            ->where('site_id', $site->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        abort_unless($form, 404);

        return $form;
    }
}
