<?php

namespace App\Policies;

use App\Models\FormSubmission;
use App\Models\User;

class FormSubmissionPolicy
{
    public function view(User $user, FormSubmission $submission): bool
    {
        return $user->hasSitePermission('form_submissions.view', $submission->site_id);
    }

    public function delete(User $user, FormSubmission $submission): bool
    {
        return $user->hasSitePermission('form_submissions.delete', $submission->site_id);
    }

    public function export(User $user, FormSubmission $submission): bool
    {
        return $user->hasSitePermission('form_submissions.export', $submission->site_id);
    }
}
