# 07 — User Roles and Permissions

## Privilege ladder

| Level | Key | Name | Scope |
|-------|-----|------|-------|
| 1 | `users.is_super_admin` | توسعه‌دهنده (سوپر ادمین فنی) | Full system incl. backups / restore / promote flags |
| 2 | `users.is_platform_admin` | مدیر کل سامانه | All sites + users/roles/content; **not** developer tools (backups) |
| 3 | `regional_admin` | مدیر سایت | Assigned site(s); unrestricted site management |
| 4 | `editor_in_chief` | سردبیر | Assigned site(s); approve / publish / schedule |
| 5 | `copy_editor` | ویراستار | Assigned site(s); edit + approve |
| 6 | `journalist` | خبرنگار | Assigned site(s); draft + submit for review |

Flags `is_super_admin` / `is_platform_admin` are account-level (not Spatie team roles).  
Spatie templates (`regional_admin`, …) are assigned **per site** via User CRUD (`site_roles` → `SyncUserSiteRoles`).

## Immediate publish
Yes — from an **approved** (or scheduled) article, users with `articles.publish` can run **انتشار فوری** (`TransitionArticleStatus::publishNow` / Filament action `publishNow`).

## Authorization dimensions

Every sensitive check answers **both**:

1. Does the user have permission `X`?
2. Is the user allowed on `site_id` of the target record?

```text
allow = user.is_active
    AND NOT user.is_blocked
    AND (user.is_super_admin OR user.is_platform_admin OR user.belongsToSite(record.site_id))
    AND (user.is_super_admin OR (user.is_platform_admin AND ability NOT IN developer_only) OR user.can(permission))
```

Developer-only abilities today: `backups.manage`.

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

## Dangerous privilege rules

1. Only the developer (`is_super_admin`) can set `is_super_admin` / `is_platform_admin` flags.
2. Platform admin cannot edit/delete developer accounts.
3. Regional Admin **cannot** assign developer/platform flags.
4. Users cannot attach themselves to sites outside their authority (except global operators).
5. Deactivated/blocked users fail `canAccessPanel()`.
6. Permission checks happen in Policies / Gate, not only Filament `->visible()`.

## Spatie teams

Enabled; `team_id` maps to `site_id` so the same person can be Journalist on Tehran and Copy Editor on Fars.
