<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $branding['meta_title'] ?? $site->name)</title>
    @if (! empty($branding['meta_description']))
        <meta name="description" content="{{ $branding['meta_description'] }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;800&family=Lalezar&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #071714;
            --primary: {{ $branding['primary'] ?? '#0f766e' }};
            --secondary: {{ $branding['secondary'] ?? '#134e4a' }};
            --accent: {{ $branding['accent'] ?? '#f59e0b' }};
            --paper: #f3f7f5;
            --muted: #5b6f6a;
            --line: rgba(7, 23, 20, 0.12);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Vazirmatn", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1200px 500px at 100% -10%, color-mix(in srgb, var(--accent) 28%, transparent), transparent 60%),
                radial-gradient(900px 420px at -10% 0%, color-mix(in srgb, var(--primary) 35%, transparent), transparent 55%),
                linear-gradient(180deg, #e8f2ef 0%, var(--paper) 42%, #eef4f1 100%);
            min-height: 100vh;
        }
        a { color: inherit; text-decoration: none; }
        .shell { width: min(1120px, calc(100% - 2rem)); margin-inline: auto; }
        .site-header {
            position: sticky; top: 0; z-index: 20;
            backdrop-filter: blur(14px);
            background: color-mix(in srgb, #071714 78%, transparent);
            color: #f8faf9;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .site-header-inner {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; padding: .9rem 0;
        }
        .brand {
            font-family: "Lalezar", "Vazirmatn", sans-serif;
            font-size: clamp(1.4rem, 2vw, 1.85rem);
            letter-spacing: .02em;
        }
        .brand span { color: var(--accent); }
        .nav { display: flex; flex-wrap: wrap; gap: .85rem 1.1rem; align-items: center; }
        .nav a { opacity: .88; font-size: .95rem; transition: opacity .2s ease, color .2s ease; }
        .nav a:hover { opacity: 1; color: var(--accent); }
        .search-pill {
            display: inline-flex; align-items: center; gap: .4rem;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px; padding: .35rem .7rem;
            background: rgba(255,255,255,.06);
        }
        .search-pill input {
            border: 0; background: transparent; color: #fff; width: 8rem;
            font: inherit; outline: none;
        }
        .search-pill button {
            border: 0; background: var(--accent); color: #1a1203;
            border-radius: 999px; padding: .25rem .7rem; font: inherit; cursor: pointer;
        }
        .hero {
            position: relative; isolation: isolate; overflow: hidden;
            min-height: min(78vh, 640px);
            display: grid; align-items: end;
            color: #f7fffc;
            background:
                linear-gradient(120deg, color-mix(in srgb, var(--secondary) 92%, #000) 0%, color-mix(in srgb, var(--primary) 70%, #04110f) 55%, #081c19 100%);
        }
        .hero::before {
            content: "";
            position: absolute; inset: 0; z-index: -1;
            background:
                radial-gradient(circle at 80% 20%, color-mix(in srgb, var(--accent) 40%, transparent), transparent 35%),
                repeating-linear-gradient(-12deg, transparent 0 18px, rgba(255,255,255,.03) 18px 19px);
            animation: drift 18s linear infinite;
        }
        @keyframes drift {
            from { transform: translate3d(0,0,0); }
            to { transform: translate3d(-40px, 18px, 0); }
        }
        .hero-inner { padding: clamp(3rem, 8vw, 6rem) 0 clamp(2.5rem, 6vw, 4rem); }
        .hero-kicker {
            display: inline-block; margin-bottom: 1rem; color: var(--accent);
            font-weight: 700; letter-spacing: .08em; font-size: .85rem;
        }
        .hero h1 {
            margin: 0 0 .8rem;
            font-family: "Lalezar", "Vazirmatn", sans-serif;
            font-size: clamp(2.6rem, 7vw, 4.8rem);
            line-height: 1.05; max-width: 12ch;
            animation: rise .8s ease both;
        }
        .hero p {
            margin: 0 0 1.6rem; max-width: 36ch; color: rgba(247,255,252,.84);
            font-size: clamp(1rem, 2vw, 1.15rem); animation: rise .9s ease .08s both;
        }
        .cta {
            display: inline-flex; align-items: center; gap: .55rem;
            background: var(--accent); color: #1a1203; font-weight: 700;
            padding: .85rem 1.25rem; border-radius: .35rem;
            box-shadow: 0 10px 30px rgba(0,0,0,.22);
            animation: rise 1s ease .14s both;
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        main { padding: 2.5rem 0 4rem; }
        .section { margin-top: 2.75rem; }
        .section-head {
            display: flex; align-items: end; justify-content: space-between;
            gap: 1rem; margin-bottom: 1.1rem;
            border-bottom: 1px solid var(--line); padding-bottom: .7rem;
        }
        .section-head h2 {
            margin: 0; font-family: "Lalezar", "Vazirmatn", sans-serif;
            font-size: clamp(1.5rem, 3vw, 2rem);
        }
        .story-list { display: grid; gap: 1rem; }
        .story {
            display: grid; grid-template-columns: 1fr auto; gap: 1rem;
            padding: 1rem 0; border-bottom: 1px solid var(--line);
            transition: transform .25s ease;
        }
        .story:hover { transform: translateX(-4px); }
        .story h3 { margin: 0 0 .35rem; font-size: 1.15rem; line-height: 1.45; }
        .story p { margin: 0; color: var(--muted); font-size: .95rem; }
        .meta { color: var(--muted); font-size: .82rem; white-space: nowrap; }
        .page-title {
            margin: 0 0 1rem;
            font-family: "Lalezar", "Vazirmatn", sans-serif;
            font-size: clamp(2rem, 5vw, 3rem);
        }
        .prose { line-height: 1.9; font-size: 1.05rem; }
        .prose img { max-width: 100%; height: auto; }
        .site-footer {
            border-top: 1px solid var(--line);
            padding: 1.5rem 0 2rem; color: var(--muted); font-size: .9rem;
        }
        @media (max-width: 720px) {
            .site-header-inner { flex-direction: column; align-items: flex-start; }
            .search-pill input { width: 6rem; }
            .story { grid-template-columns: 1fr; }
        }
    </style>
    @stack('head')
</head>
<body>
    <header class="site-header">
        <div class="shell site-header-inner">
            <a class="brand" href="{{ route('public.home', $site) }}">
                {{ $branding['title'] ?? $site->name }} <span>·</span>
            </a>
            <nav class="nav" aria-label="منوی اصلی">
                @foreach ($menu ?? [] as $item)
                    @if ($item['url'])
                        <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                    @endif
                @endforeach
                <a href="{{ route('public.news', $site) }}">آرشیو اخبار</a>
                <form class="search-pill" action="{{ route('public.search', $site) }}" method="get">
                    <input type="search" name="q" placeholder="جستجو" value="{{ request('q') }}" aria-label="جستجو">
                    <button type="submit">برو</button>
                </form>
            </nav>
        </div>
    </header>

    @yield('hero')

    <main class="shell">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="shell">
            © {{ now()->format('Y') }} {{ $branding['title'] ?? $site->name }} — سامانه خبری پلوتون
        </div>
    </footer>
</body>
</html>
