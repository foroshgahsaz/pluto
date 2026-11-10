@extends('public.layout')

@section('title', $article->meta_title ?: $article->title)

@section('content')
    <article>
        <p class="meta">
            {{ optional($article->published_at)->format('Y/m/d H:i') }}
            @if ($article->category)
                · <a href="{{ route('public.category', [$site, $article->category->slug]) }}">{{ $article->category->name }}</a>
            @endif
            @if ($article->author)
                · {{ $article->author->name }}
            @endif
        </p>
        <h1 class="page-title">{{ $article->title }}</h1>
        @if ($article->summary)
            <p style="color:var(--muted); font-size:1.1rem; margin-top:-.4rem;">{{ $article->summary }}</p>
        @endif
        @if ($article->featuredMedia?->publicUrl())
            <p style="margin:1.2rem 0;">
                <img src="{{ $article->featuredMedia->publicUrl() }}" alt="{{ $article->featuredMedia->alt_text ?: $article->title }}" style="width:100%; border-radius:.4rem;">
            </p>
        @endif
        <div class="prose">{!! app(\App\Support\Security\HtmlSanitizer::class)->clean($article->body) !!}</div>
    </article>
@endsection
