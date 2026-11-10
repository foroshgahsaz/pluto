@extends('public.layout')

@section('title', 'آرشیو اخبار — '.$branding['title'])

@section('content')
    <h1 class="page-title">آرشیو اخبار</h1>
    <div class="story-list">
        @forelse ($articles as $article)
            <a class="story" href="{{ route('public.article', [$site, $article->slug]) }}">
                <div>
                    <h3>{{ $article->title }}</h3>
                    @if ($article->summary)
                        <p>{{ \Illuminate\Support\Str::limit($article->summary, 160) }}</p>
                    @endif
                </div>
                <div class="meta">{{ optional($article->published_at)->format('Y/m/d H:i') }}</div>
            </a>
        @empty
            <p class="meta">خبر منتشرشده‌ای یافت نشد.</p>
        @endforelse
    </div>
    <div style="margin-top:1.5rem">{{ $articles->links() }}</div>
@endsection
