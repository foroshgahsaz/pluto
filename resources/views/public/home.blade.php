@extends('public.layout')

@section('title', $branding['meta_title'])

@section('hero')
<section class="hero">
    <div class="shell hero-inner">
        <div class="hero-kicker">سامانه خبری پلوتون</div>
        <h1>{{ $branding['title'] }}</h1>
        <p>{{ $branding['tagline'] !== '' ? $branding['tagline'] : 'تازه‌ترین روایت‌ها از میدان خبر، با تمرکز روی وضوح و سرعت انتشار.' }}</p>
        <a class="cta" href="{{ route('public.news', $site) }}">مشاهده آرشیو اخبار</a>
    </div>
</section>
@endsection

@section('content')
    @forelse ($sections as $block)
        <section class="section">
            <div class="section-head">
                <h2>{{ $block['section']->title }}</h2>
            </div>
            <div class="story-list">
                @forelse ($block['articles'] as $article)
                    <a class="story" href="{{ route('public.article', [$site, $article->slug]) }}">
                        <div>
                            <h3>{{ $article->title }}</h3>
                            @if ($article->summary)
                                <p>{{ \Illuminate\Support\Str::limit($article->summary, 140) }}</p>
                            @endif
                        </div>
                        <div class="meta">{{ optional($article->published_at)->format('Y/m/d') }}</div>
                    </a>
                @empty
                    <p class="meta">هنوز خبری در این بخش نیست.</p>
                @endforelse
            </div>
        </section>
    @empty
        <section class="section">
            <div class="section-head"><h2>آخرین اخبار</h2></div>
            <p class="meta">بخش‌های صفحه اصلی هنوز پیکربندی نشده‌اند.</p>
        </section>
    @endforelse
@endsection
