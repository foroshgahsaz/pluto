<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($sites as $site)
    <sitemap>
        <loc>{{ route('public.sitemap', $site) }}</loc>
        @if ($site->updated_at)
        <lastmod>{{ $site->updated_at->toAtomString() }}</lastmod>
        @endif
    </sitemap>
@endforeach
</sitemapindex>
