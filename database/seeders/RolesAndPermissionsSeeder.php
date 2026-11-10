<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'sites.view', 'sites.create', 'sites.update', 'sites.delete', 'sites.activate',
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.assign_roles',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'articles.view', 'articles.create', 'articles.update', 'articles.delete',
            'articles.publish', 'articles.approve', 'articles.schedule', 'articles.feature',
            'media.view', 'media.upload', 'media.update', 'media.delete', 'media.download_private',
            'forms.view', 'forms.create', 'forms.update', 'forms.delete',
            'form_submissions.view', 'form_submissions.export', 'form_submissions.delete',
            'settings.manage', 'themes.manage', 'menus.manage', 'pages.manage',
            'reports.view', 'notifications.manage', 'backups.manage', 'activity_logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Elevated flags (not Spatie roles):
        // - users.is_super_admin  => technical developer (all abilities incl. backups)
        // - users.is_platform_admin => content platform chief (all sites, all content abilities except backups)

        // Global role templates (team_id null). Assignments are site-scoped via teams.
        $rolePermissions = [
            'regional_admin' => $permissions,
            'editor_in_chief' => [
                'categories.view', 'categories.create', 'categories.update', 'categories.delete',
                'articles.view', 'articles.create', 'articles.update', 'articles.delete',
                'articles.publish', 'articles.approve', 'articles.schedule', 'articles.feature',
                'media.view', 'media.upload', 'media.update', 'media.delete',
                'forms.view', 'forms.create', 'forms.update',
                'form_submissions.view', 'form_submissions.export',
                'reports.view',
            ],
            'journalist' => [
                'categories.view',
                'articles.view', 'articles.create', 'articles.update',
                'media.view', 'media.upload',
            ],
            'copy_editor' => [
                'categories.view',
                'articles.view', 'articles.update', 'articles.approve',
                'media.view', 'media.upload',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($perms);
        }
    }
}
