# 33 — SEO & Syndication

**Status:** Implemented — Stage 11

## Endpoints
| URL | Purpose |
|-----|---------|
| `/{site}/rss.xml` | RSS 2.0 feed of latest published articles (site-scoped) |
| `/{site}/sitemap.xml` | URL set: home, news, articles, categories, pages, forms |
| `/sitemap.xml` | Sitemap index of all **active** sites |
| `/robots.txt` | Allow public; disallow `/admin` + `/livewire`; list sitemaps |

## Rules
- Draft/inactive content excluded
- Inactive sites 404 on per-site feeds/sitemaps
- Public layout `<link rel="alternate" type="application/rss+xml">`

## Tests
`tests/Feature/PublicSeoSyndicationTest.php`
