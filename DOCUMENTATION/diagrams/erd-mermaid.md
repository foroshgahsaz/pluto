# 05 — Database ERD

Mermaid diagrams for Stage 1 design review. Source copies also live under `DOCUMENTATION/diagrams/`.

## 1. Core tenancy & identity

```mermaid
erDiagram
    SITE ||--o{ SITE_USER : has
    USER ||--o{ SITE_USER : belongs
    SITE ||--o| SITE : parent
    SITE }o--o| THEME : uses
    SITE ||--o{ SITE_SETTING : configures

    SITE {
      bigint id PK
      bigint parent_id FK
      string type
      string slug UK
      boolean is_active
    }
    USER {
      bigint id PK
      string email UK
      boolean is_active
      boolean is_blocked
    }
    SITE_USER {
      bigint id PK
      bigint site_id FK
      bigint user_id FK
    }
    THEME {
      bigint id PK
      string key UK
    }
    SITE_SETTING {
      bigint id PK
      bigint site_id FK
      string group
      string key
    }
```

## 2. Editorial domain

```mermaid
erDiagram
    SITE ||--o{ CATEGORY : owns
    SITE ||--o{ TAG : owns
    SITE ||--o{ AUTHOR : owns
    SITE ||--o{ ARTICLE : owns
    CATEGORY ||--o{ CATEGORY : parent
    CATEGORY ||--o{ ARTICLE : categorizes
    AUTHOR ||--o{ ARTICLE : writes
    ARTICLE ||--o{ ARTICLE_REVISION : versions
    ARTICLE ||--o{ ARTICLE_ATTACHMENT : attaches
    ARTICLE }o--o{ TAG : tagged
    MEDIA ||--o{ ARTICLE_ATTACHMENT : file
    ARTICLE }o--o| MEDIA : featured

    ARTICLE {
      bigint id PK
      bigint site_id FK
      string slug
      string status
      datetime scheduled_for
      datetime published_at
    }
```

## 3. Media

```mermaid
erDiagram
    SITE ||--o{ MEDIA_FOLDER : owns
    SITE ||--o{ MEDIA : owns
    MEDIA_FOLDER ||--o{ MEDIA_FOLDER : parent
    MEDIA_FOLDER ||--o{ MEDIA : contains
    USER ||--o{ MEDIA : uploads
```

## 4. Forms

```mermaid
erDiagram
    SITE ||--o{ FORM : owns
    FORM ||--o{ FORM_FIELD : defines
    FORM ||--o{ FORM_SUBMISSION : receives
    FORM_SUBMISSION ||--o{ FORM_SUBMISSION_FILE : files
    MEDIA ||--o{ FORM_SUBMISSION_FILE : stores
```

## 5. Menus / pages / homepage

```mermaid
erDiagram
    SITE ||--o{ PAGE : owns
    SITE ||--o{ MENU : owns
    SITE ||--o{ HOMEPAGE_SECTION : owns
    MENU ||--o{ MENU_ITEM : contains
    MENU_ITEM ||--o{ MENU_ITEM : parent
```

## 6. Security & ops

```mermaid
erDiagram
    USER ||--o{ LOGIN_EVENT : generates
    USER ||--o{ ACTIVITY_LOG : causes
    SITE ||--o{ ACTIVITY_LOG : context
    USER ||--o{ BACKUP : triggers
```

## Relationship notes

- **Cardinality:** One site has many articles; one article belongs to exactly one site.
- **Cross-site joins are forbidden** in normal queries; Super Admin reporting services may aggregate with explicit authorization.
- **Permission teams:** Spatie `team_id` aligns with `sites.id` (not drawn as custom tables above).
