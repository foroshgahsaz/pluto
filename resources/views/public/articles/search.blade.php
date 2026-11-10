@extends('public.layout')

@section('title', 'جستجو — '.$branding['title'])

@section('content')
    <h1 class="page-title">جستجو</h1>
    @if ($term === '')
        <p class="meta">عبارت جستجو را وارد کنید.</p>
    @else
        <p class="meta">نتایج برای «{{ $term }}»</p>
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
                <p class="meta">نتیجه‌ای پیدا نشد.</p>
            @endforelse
        </div>
        @if ($articles)
            <div style="margin-top:1.5rem">{{ $articles->withQueryString()->links() }}</div>
        @endif
    @endif
@endsection
