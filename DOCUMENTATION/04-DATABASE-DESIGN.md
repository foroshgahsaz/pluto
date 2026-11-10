# 04 — Database Design

**Engine:** MySQL 8.0+ / InnoDB / `utf8mb4_unicode_ci`  
**Strategy:** Single shared database, logical multi-tenancy via `site_id`  
**Status:** Design only — migrations not created until STAGE 2 approval

---

## 1. Design principles

1. Every site-scoped table has a non-null `site_id` FK to `sites.id` (except global catalogs noted below).
2. Prefer FK constraints over soft application-only integrity.
3. Soft deletes for editorial/content entities that need recovery; hard delete for ephemeral logs only when retention policy says so.
4. Unique constraints are **site-aware** where needed (`UNIQUE(site_id, slug)`).
5. Indexes support tenant filters first: `(site_id, ...)`.
6. No secrets in tables as plaintext (password hashes only via Laravel hasher).

### Global vs site-scoped

| Global (no site_id) | Site-scoped (has site_id) |
|---------------------|---------------------------|
| `users` (identity is global) | `categories`, `tags`, `articles`, … |
| `permissions` | `menus`, `menu_items`, `pages` |
| `themes` (catalog) | `site_settings`, `media`, `forms` |
| `roles` (see note on teams) | `form_submissions`, `homepage_sections` |
| `activity_log` (optional site_id column) | `notifications` (site optional) |

**Note:** With Spatie permission **teams** enabled, `roles` / model_has_roles carry `team_id` mapped to `site_id`. Base permission names remain global; role assignments can be site-scoped. See ADR-002.

---

## 2. Core tables

### 2.1 `sites`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | |
| parent_id | BIGINT NULL FK→sites | Optional hierarchy; regional → central |
| type | ENUM(`central`,`regional`) | Exactly one active central recommended |
| name | VARCHAR(255) | |
| slug | VARCHAR(100) UNIQUE | URL subdirectory; central may use empty/`/` |
| domain | VARCHAR(255) NULL | Reserved for future host mapping |
| locale | VARCHAR(10) DEFAULT `fa` | |
| timezone | VARCHAR(64) DEFAULT `Asia/Tehran` | |
| is_active | BOOLEAN DEFAULT true | Soft disable without delete |
| theme_id | BIGINT NULL FK→themes | Selected theme |
| settings_json | JSON NULL | Rare overrides; prefer `site_settings` |
| created_at / updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULL | Soft delete |

**Indexes:** unique `slug`; index `(is_active)`; index `parent_id`.

### 2.2 `users`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | |
| name | VARCHAR(255) | |
| email | VARCHAR(255) UNIQUE | |
| email_verified_at | TIMESTAMP NULL | |
| password | VARCHAR(255) | hashed |
| is_active | BOOLEAN DEFAULT true | Deactivation |
| is_blocked | BOOLEAN DEFAULT false | Hard block |
| last_login_at | TIMESTAMP NULL | |
| last_login_ip | VARCHAR(45) NULL | |
| remember_token | VARCHAR(100) NULL | |
| timestamps / deleted_at | | Soft delete optional |

### 2.3 `site_user` (membership)

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | |
| site_id | FK | |
| user_id | FK | |
| is_primary | BOOLEAN DEFAULT false | Default admin context |
| created_at | TIMESTAMP | |

**Unique:** `(site_id, user_id)`.

### 2.4 Spatie permission tables (teams enabled)

Standard Spatie schema with `team_foreign_key` = `site_id`:

- `roles` (+ `site_id` / team column)
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

### 2.5 Editorial

#### `categories`
`id`, `site_id`, `parent_id` NULL, `name`, `slug`, `description` NULL, `sort_order`, `is_active`, timestamps, `deleted_at`  
**Unique:** `(site_id, slug)`

#### `tags`
`id`, `site_id`, `name`, `slug`, timestamps  
**Unique:** `(site_id, slug)`

#### `authors` (journalist profiles, may link to users)
`id`, `site_id`, `user_id` NULL, `display_name`, `bio` NULL, `avatar_media_id` NULL, timestamps, `deleted_at`

#### `articles`
| Column | Notes |
|--------|-------|
| site_id | Required |
| category_id | FK nullable or required per policy |
| author_id | FK authors |
| editor_id | content manager user_id NULL |
| title / slug / summary / body | body as LONGTEXT |
| status | enum: draft, in_review, approved, scheduled, published, rejected, archived |
| is_featured / is_selected | booleans |
| published_at / scheduled_for | nullable |
| published_by | user_id NULL |
| meta_title / meta_description | SEO fields |
| featured_media_id | FK media NULL |
| timestamps / deleted_at | |

**Unique:** `(site_id, slug)`  
**Indexes:** `(site_id, status, published_at)`, `(site_id, is_featured)`, `(scheduled_for, status)`

#### `article_tag` — `article_id`, `tag_id` (unique pair)
#### `article_keywords` — `id`, `article_id`, `keyword`
#### `article_revisions` — `id`, `article_id`, `site_id`, `user_id`, `version`, `snapshot_json`, `created_at`
#### `article_attachments` — `id`, `article_id`, `site_id`, `media_id`, `sort_order`

### 2.6 Media

#### `media_folders`
`id`, `site_id`, `parent_id` NULL, `name`, timestamps, `deleted_at`  
**Unique:** `(site_id, parent_id, name)`

#### `media`
| Column | Notes |
|--------|-------|
| site_id | Required |
| folder_id | NULL |
| disk | `public` or `private` |
| path | storage path |
| original_name | sanitized display name |
| mime_type / extension / size | validated server-side |
| checksum | sha256 optional |
| visibility | public/private |
| uploaded_by | user_id |
| timestamps / deleted_at | |

**Indexes:** `(site_id, folder_id)`, `(site_id, mime_type)`, fulltext/name search as needed.

### 2.7 Pages / menus / homepage

#### `pages`
`id`, `site_id`, `title`, `slug`, `body`, `status`, `published_at`, timestamps, `deleted_at`  
**Unique:** `(site_id, slug)`

#### `menus`
`id`, `site_id`, `name`, `location` (primary/secondary/footer), timestamps  
**Unique:** `(site_id, location)` or `(site_id, name)` per product choice

#### `menu_items`
`id`, `menu_id`, `site_id`, `parent_id` NULL, `title`, `type` (url/page/category/article), `reference_id` NULL, `url` NULL, `sort_order`, `is_active`, timestamps

#### `homepage_sections`
`id`, `site_id`, `key`, `title`, `type` (latest/featured/selected/category/custom), `config_json`, `sort_order`, `is_active`, timestamps  
**Unique:** `(site_id, key)`

### 2.8 Themes & settings

#### `themes`
`id`, `key` UNIQUE, `name`, `preview_image` NULL, `is_active`, `config_schema_json`, timestamps

#### `site_settings`
`id`, `site_id`, `group` (branding/colors/seo/general), `key`, `value` JSON, timestamps  
**Unique:** `(site_id, group, key)`

### 2.9 Forms

#### `forms`
`id`, `site_id`, `title`, `slug`, `description`, `success_message`, `is_active`, `is_multi_step`, timestamps, `deleted_at`  
**Unique:** `(site_id, slug)`

#### `form_fields`
`id`, `form_id`, `site_id`, `step` INT DEFAULT 1, `type` (text, textarea, number, date, select, multiselect, file, image, …), `name`, `label`, `help_text`, `placeholder`, `is_required`, `validation_rules_json`, `options_json`, `visibility_rules_json`, `sort_order`, timestamps

#### `form_submissions`
`id`, `form_id`, `site_id`, `user_id` NULL, `payload_json`, `ip` NULL, `user_agent` NULL, `created_at`

#### `form_submission_files`
`id`, `submission_id`, `site_id`, `form_field_id`, `media_id`, timestamps

### 2.10 Notifications & audit & backups

#### `notifications` — Laravel database notifications (UUID id) + optional `site_id` in data payload

#### `activity_log` — Spatie activitylog (+ custom `site_id` property)

#### `login_events` (recommended dedicated table for auth security)
`id`, `user_id` NULL, `email_attempted`, `event` (success/failure/logout/blocked), `ip`, `user_agent`, `created_at`

#### `backups` (metadata only; files on disk/remote)
`id`, `disk`, `path`, `size`, `type` (db/files/full), `status`, `triggered_by`, `started_at`, `finished_at`, `checksum` NULL, timestamps

---

## 3. Soft deletes policy

| Soft delete | Hard delete / retention |
|-------------|-------------------------|
| sites, users, articles, categories, media, forms, pages | form_submissions: retain per policy; activity_log: retention job; login_events: retention job |

---

## 4. Important integrity rules

1. Article `category_id` must belong to same `site_id` (enforced in service + optional DB trigger later; app-level mandatory in MVP).
2. Media attached to article must share `site_id`.
3. Form field `form_id` and submission `form_id` must share `site_id`.
4. Super Admin operations that cross sites must not disable FK integrity.

---

## 5. Migration plan (after approval)

1. Framework default tables (users, cache, jobs, …)
2. `sites`, `themes`, `site_settings`
3. Permission tables + `site_user`
4. Media folders/media
5. Editorial tables
6. Pages/menus/homepage
7. Forms
8. Auth security + backups metadata
9. Seeders: central site, super admin, roles/permissions, default theme

No migration will be executed until STAGE 2 is explicitly approved.
