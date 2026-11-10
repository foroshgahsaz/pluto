@extends('public.layout')

@section('title', $category->name.' — '.$branding['title'])

@section('content')
    <h1 class="page-title">{{ $category->name }}</h1>
    @if ($category->description)
        <p class="meta" style="margin-bottom:1rem;">{{ $category->description }}</p>
    @endif
    <div class="story-list">
        @forelse ($articles as $article)
            <a class="story" href="{{ route('public.article', [$site, $article->slug]) }}">
                <div>
                    <h3>{{ $article->title }}</h3>
                    @if ($article->summary)
                        <p>{{ \Illuminate\Support\Str::limit($article->summary, 160) }}</p>
                    @endif
                </div>
                <div class="meta">{{ optional($article->published_at)->format('Y/m/d') }}</div>
            </a>
        @empty
            <p class="meta">خبری در این دسته نیست.</p>
        @endforelse
    </div>
    <div style="margin-top:1.5rem">{{ $articles->links() }}</div>
@endsection
