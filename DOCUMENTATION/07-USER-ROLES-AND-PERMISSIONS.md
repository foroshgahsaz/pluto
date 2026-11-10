# 07 — User Roles and Permissions

## Initial roles

| Role key | Name | Typical scope |
|----------|------|---------------|
| `super_admin` | System Super Administrator | All sites; manage sites/users/roles/backups |
| `regional_admin` | Regional Administrator | Assigned sites; manage site users/content/settings |
| `editor_in_chief` | Editor-in-Chief | Assigned sites; approve/publish/manage editorial |
| `journalist` | Journalist | Assigned sites; create/edit own drafts; submit for review |
| `copy_editor` | Copy Editor | Assigned sites; edit content; limited publish rights (configurable) |

Custom roles are supported via Spatie + Filament Role resource (Super Admin / Regional Admin as permitted).

## Authorization dimensions

Every sensitive check answers **both**:

1. Does the user have permission `X`?
2. Is the user allowed on `site_id` of the target record?

```text
allow = user.is_active
    AND NOT user.is_blocked
    AND (user.is_super_admin OR user.belongsToSite(record.site_id))
    AND user.can(permission)
```

## Permission catalog (initial)

Format: `{resource}.{action}`

### Sites
`sites.view`, `sites.create`, `sites.update`, `sites.delete`, `sites.activate`

### Users & roles
`users.view`, `users.create`, `users.update`, `users.delete`, `users.assign_roles`  
`roles.view`, `roles.create`, `roles.update`, `roles.delete`

### Editorial
`articles.view`, `articles.create`, `articles.update`, `articles.delete`  
`articles.publish`, `articles.approve`, `articles.schedule`, `articles.feature`  
`categories.*`, `tags.*`, `authors.*`

### Media
`media.view`, `media.upload`, `media.update`, `media.delete`, `media.download_private`

### Forms
`forms.view`, `forms.create`, `forms.update`, `forms.delete`  
`form_submissions.view`, `form_submissions.export`, `form_submissions.delete`

### Settings / themes / menus / pages
`settings.manage`, `themes.manage`, `menus.manage`, `pages.manage`

### Reports / notifications / backups / audit
`reports.view`, `notifications.manage`, `backups.manage`, `activity_logs.view`

Exact seeding list will be finalized in STAGE 2 and mirrored here.

## Dangerous privilege rules

1. Regional Admin **cannot** assign `super_admin`.
2. Users cannot grant permissions they do not possess (except Super Admin).
3. Users cannot attach themselves to sites outside their authority.
4. Deactivated/blocked users fail `canAccessPanel()` and API/admin middleware.
5. Permission checks happen in Policies, not only Filament `->visible()`.

## Spatie teams proposal

Enable teams; map `team_id` → `site_id`.

**Why:** Same person can be Journalist on Tehran and Copy Editor on Fars.

**Alternative (simpler MVP):** Global roles + `site_user` membership only (role identical on all assigned sites).

Owner must choose in [28-APPROVAL-REQUEST.md](28-APPROVAL-REQUEST.md).
