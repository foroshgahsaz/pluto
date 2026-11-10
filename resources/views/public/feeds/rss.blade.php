<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>{{ $branding['title'] }}</title>
    <link>{{ $channelLink }}</link>
    <description>{{ $branding['meta_description'] !== '' ? $branding['meta_description'] : ($branding['tagline'] ?: $branding['title']) }}</description>
    <language>fa-IR</language>
    <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
    <atom:link href="{{ $feedLink }}" rel="self" type="application/rss+xml" />
@foreach ($articles as $article)
    <item>
        <title>{{ $article->title }}</title>
        <link>{{ route('public.article', [$site, $article->slug]) }}</link>
        <guid isPermaLink="true">{{ route('public.article', [$site, $article->slug]) }}</guid>
        <pubDate>{{ optional($article->published_at)->toRfc2822String() }}</pubDate>
        @if ($article->summary)
        <description><![CDATA[{{ $article->summary }}]]></description>
        @endif
        @if ($article->category)
        <category>{{ $article->category->name }}</category>
        @endif
    </item>
@endforeach
</channel>
</rss>
