<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGlobalOperator() || $user->sites()->exists();
    }

    public function view(User $user, Form $form): bool
    {
        return $user->hasSitePermission('forms.view', $form->site_id);
    }

    public function create(User $user): bool
    {
        if ($user->isGlobalOperator()) {
            return true;
        }

        foreach ($user->authorizedSiteIds() as $siteId) {
            if ($user->hasSitePermission('forms.create', (int) $siteId)) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, Form $form): bool
    {
        return $user->hasSitePermission('forms.update', $form->site_id);
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->hasSitePermission('forms.delete', $form->site_id);
    }

    public function viewSubmissions(User $user, Form $form): bool
    {
        return $user->hasSitePermission('form_submissions.view', $form->site_id);
    }

    public function exportSubmissions(User $user, Form $form): bool
    {
        return $user->hasSitePermission('form_submissions.export', $form->site_id);
    }

    public function deleteSubmissions(User $user, Form $form): bool
    {
        return $user->hasSitePermission('form_submissions.delete', $form->site_id);
    }
}
