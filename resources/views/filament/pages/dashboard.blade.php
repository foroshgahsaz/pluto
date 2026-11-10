@php
    $d = $this->dashboardData();
    $stats = $d['stats'];
    $queue = $d['queue'];
    $visits = $d['visits'];
    $provincial = $d['provincial'];
    $performance = $d['performance'];
    $fmt = fn (int|float $n) => \App\Support\PersianDate::persianDigits(number_format((int) $n));
    $pct = fn (float $n) => \App\Support\PersianDate::persianDigits(rtrim(rtrim(number_format($n, 1), '0'), '.'));

    $points = $visits['data'];
    $count = max(1, count($points));
    $max = max(1, (int) $visits['max']);
    $chartW = 520; $chartH = 180; $padL = 42; $padR = 10; $padT = 20; $padB = 25;
    $innerW = $chartW - $padL - $padR;
    $innerH = $chartH - $padT - $padB;
    $coords = [];
    foreach ($points as $i => $value) {
        $x = $padL + ($count === 1 ? $innerW / 2 : ($i / max(1, $count - 1)) * $innerW);
        $y = $padT + $innerH - (($value / $max) * $innerH);
        $coords[] = [round($x, 1), round($y, 1)];
    }
    $line = '';
    foreach ($coords as $i => [$x, $y]) {
        $line .= ($i === 0 ? 'M' : ' L')."{$x} {$y}";
    }
    $area = $coords === []
        ? ''
        : $line.' L'.$coords[array_key_last($coords)][0].' '.($padT + $innerH).' L'.$coords[0][0].' '.($padT + $innerH).' Z';
    $labelIdx = $count <= 1
        ? [0]
        : array_values(array_unique([0, (int) floor(($count - 1) / 3), (int) floor(2 * ($count - 1) / 3), $count - 1]));

    $vEnd = $performance['visitors'];
    $cEnd = $vEnd + $performance['content'];
    $eEnd = $cEnd + $performance['engagement'];
    $donut = "conic-gradient(#168de0 0 {$vEnd}%, #21bac5 {$vEnd}% {$cEnd}%, #efc66f {$cEnd}% {$eEnd}%, #e6eff6 {$eEnd}%)";
@endphp

<x-filament-panels::page>
    <div class="pluto-dash" dir="rtl">
        <section class="welcome">
            <div>
                <h1>سلام، {{ $d['userName'] }}</h1>
                <p>به سامانه مدیریت رسانه‌های خبری خوش آمدید</p>
            </div>
            <div class="welcome-date">{{ $d['nowLabel'] }}　✦</div>
        </section>

        <section class="queue-panel">
            <div class="queue-head">
                <div>
                    <h3>صف خبر</h3>
                    <p>برنامه انتشار و پیگیری خبرهای امروز و فردا</p>
                </div>
                <div class="queue-actions">
                    <a href="{{ $d['articlesUrl'] }}" class="queue-filter">همه خبرها</a>
                    <span class="qtag ready">● به‌روز</span>
                </div>
            </div>
            <div class="queue-days">
                @foreach (['today' => $queue['today'], 'tomorrow' => $queue['tomorrow']] as $day)
                    <div class="queue-day">
                        <div class="queue-day-title">
                            <div>
                                <strong>{{ $day['label'] }}</strong>
                                <small>{{ $day['date'] }}</small>
                            </div>
                            <span class="queue-count">{{ \App\Support\PersianDate::persianDigits((string) count($day['items'])) }} خبر</span>
                        </div>
                        <div class="queue-items">
                            @forelse ($day['items'] as $item)
                                <button type="button" class="queue-item" wire:click="openNews({{ $item['id'] }})">
                                    <span class="queue-num">{{ $item['num'] }}</span>
                                    <span class="queue-icon">{{ $item['status_key'] === 'ready' ? '↗' : ($item['status_key'] === 'urgent' ? '◈' : '●') }}</span>
                                    <span class="queue-copy">
                                        <strong>{{ $item['title'] }}</strong>
                                        <small>{{ $item['summary'] }}</small>
                                        <span class="queue-meta">
                                            <span class="qtag time">{{ $item['time'] }}</span>
                                            <span class="qtag">{{ $item['category'] }}</span>
                                            <span class="qtag {{ $item['status_key'] }}">● {{ $item['status'] }}</span>
                                        </span>
                                    </span>
                                    <span class="queue-chevron">‹</span>
                                </button>
                            @empty
                                <div class="empty">خبری برای این روز در صف نیست</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="stats">
            <article class="stat" style="--wash:#fff1d7;--accent:#d49a32">
                <div class="stat-top"><span class="stat-label">تعداد سایت‌های فعال</span><span class="stat-icon">◎</span></div>
                <strong>{{ $fmt($stats['active_sites']) }}</strong>
                <small>↑ {{ $fmt($stats['sites_delta']) }} سایت <em>در ماه جاری</em></small>
            </article>
            <article class="stat" style="--wash:#dff8f7;--accent:#12aeb4">
                <div class="stat-top"><span class="stat-label">تعداد کاربران</span><span class="stat-icon">♙</span></div>
                <strong>{{ $fmt($stats['users']) }}</strong>
                <small>↑ {{ $pct($stats['users_delta_pct']) }}٪ <em>نسبت به ماه قبل</em></small>
            </article>
            <article class="stat" style="--wash:#e1f2ff;--accent:#168de0">
                <div class="stat-top"><span class="stat-label">کل اخبار منتشرشده</span><span class="stat-icon">▤</span></div>
                <strong>{{ $fmt($stats['published']) }}</strong>
                <small>↑ {{ $pct($stats['published_delta_pct']) }}٪ <em>نسبت به ماه قبل</em></small>
            </article>
            <article class="stat" style="--wash:#fff4df;--accent:#d49a32">
                <div class="stat-top"><span class="stat-label">کل بازدیدها</span><span class="stat-icon">◉</span></div>
                <strong>{{ $fmt($stats['views']) }}</strong>
                <small>↑ {{ $pct($stats['views_delta_pct']) }}٪ <em>نسبت به ماه قبل</em></small>
            </article>
        </section>

        <section class="grid-main">
            <article class="panel">
                <div class="panel-head">
                    <h3>نمودار بازدیدها</h3>
                    <select class="select-small" wire:model.live="visitsPeriod">
                        <option value="30">۳۰ روز اخیر</option>
                        <option value="7">۷ روز اخیر</option>
                        <option value="365">۱۲ ماه اخیر</option>
                    </select>
                </div>
                <div class="chart-wrap">
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH + 20 }}" preserveAspectRatio="none" aria-label="نمودار بازدیدها">
                        <defs>
                            <linearGradient id="plutoArea" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0" stop-color="#21b7c8" stop-opacity=".30"/>
                                <stop offset="1" stop-color="#21b7c8" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <g>
                            @foreach ([0, 0.25, 0.5, 0.75, 1] as $step)
                                @php $y = $padT + $innerH * (1 - $step); @endphp
                                <path class="gridline" d="M{{ $padL }} {{ $y }}H{{ $chartW - $padR }}"/>
                                <text class="chart-label" x="{{ $padL - 8 }}" y="{{ $y + 3 }}" text-anchor="end">{{ \App\Support\PersianDate::persianDigits((string) (int) round($max * $step)) }}</text>
                            @endforeach
                        </g>
                        @if ($line !== '')
                            <path class="chart-area" d="{{ $area }}"/>
                            <path class="chart-line" d="{{ $line }}"/>
                            <g>
                                @foreach ($coords as [$x, $y])
                                    <circle class="chart-dot" cx="{{ $x }}" cy="{{ $y }}" r="4"/>
                                @endforeach
                            </g>
                        @endif
                        <g>
                            @foreach ($labelIdx as $idx)
                                @if (isset($coords[$idx], $visits['labels'][$idx]))
                                    <text class="chart-label" x="{{ $coords[$idx][0] }}" y="{{ $chartH + 15 }}" text-anchor="{{ $idx === $count - 1 ? 'end' : 'middle' }}">{{ $visits['labels'][$idx] }}</text>
                                @endif
                            @endforeach
                        </g>
                    </svg>
                </div>
            </article>

            <article class="panel">
                <div class="panel-head">
                    <h3>وضعیت سایت‌های استانی</h3>
                    <a class="link" href="{{ $d['sitesUrl'] }}">مشاهده جزئیات ←</a>
                </div>
                <div class="map-placeholder">
                    <div class="map-shape">✥</div>
                    <div class="map-note">نمای آماری وضعیت سایت‌های مناطق</div>
                </div>
                <div class="legend">
                    <span><i class="dot" style="background:#19b9b4"></i>فعال {{ \App\Support\PersianDate::persianDigits((string) $provincial['active']) }}</span>
                    <span><i class="dot" style="background:#efb54d"></i>در حال راه‌اندازی {{ \App\Support\PersianDate::persianDigits((string) $provincial['setup']) }}</span>
                    <span><i class="dot" style="background:#8ca4bd"></i>غیرفعال {{ \App\Support\PersianDate::persianDigits((string) $provincial['inactive']) }}</span>
                </div>
            </article>

            <article class="panel performance-panel">
                <div class="panel-head">
                    <h3>عملکرد کلی سامانه</h3>
                    <span style="color:#168de0">◉</span>
                </div>
                <div class="performance">
                    <div class="donut" style="background: {{ $donut }}">
                        <strong>{{ \App\Support\PersianDate::persianDigits((string) $performance['score']) }}٪</strong>
                    </div>
                    <div class="legend-list">
                        <div><i style="background:#168de0"></i>بازدیدکنندگان</div>
                        <div><i style="background:#21bac5"></i>تولید محتوا</div>
                        <div><i style="background:#efc66f"></i>تعامل کاربران</div>
                    </div>
                </div>
                <div style="text-align:center;font-size:10px;color:#8298b0">
                    افزایش نسبت به ماه قبل
                    <b style="color:#10a77d;font-size:14px">↑ {{ $pct($performance['growth_pct']) }}٪</b>
                </div>
            </article>
        </section>

        <section class="bottom-grid">
            <article class="panel">
                <div class="panel-head">
                    <h3>آخرین فعالیت‌ها</h3>
                    <a class="link" href="{{ \App\Filament\Resources\ActivityLogs\ActivityLogResource::getUrl('index') }}">مشاهده همه</a>
                </div>
                <div class="activity-list">
                    @forelse ($d['activities'] as $activity)
                        <div class="activity">
                            <span class="bubble">{{ $activity['icon'] }}</span>
                            <b>{{ $activity['text'] }}</b>
                            <time>{{ $activity['time'] }}</time>
                        </div>
                    @empty
                        <div class="empty">هنوز فعالیتی ثبت نشده است</div>
                    @endforelse
                </div>
            </article>

            <article class="panel">
                <div class="panel-head">
                    <h3>جدیدترین اخبار</h3>
                    <a class="link" href="{{ $d['articlesUrl'] }}">مشاهده همه</a>
                </div>
                @forelse ($d['latestNews'] as $news)
                    <a class="news-card" href="{{ $news['url'] }}">
                        <div class="news-thumb">{{ $news['thumb'] }}</div>
                        <div>
                            <h4>{{ $news['title'] }}</h4>
                            <p>{{ $news['summary'] }}</p>
                        </div>
                    </a>
                @empty
                    <div class="empty">خبر منتشرشده‌ای وجود ندارد</div>
                @endforelse
            </article>

            <article class="panel quick-panel">
                <div class="panel-head">
                    <h3>مدیریت سریع</h3>
                    <span style="color:#e3ad43">ϟ</span>
                </div>
                <div class="quick-grid">
                    @foreach ($d['quickActions'] as $action)
                        <a class="quick" href="{{ $action['url'] }}">
                            <b>{{ $action['icon'] }}</b>
                            <span>{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </article>
        </section>

        @if ($d['selectedNews'])
            @php $n = $d['selectedNews']; @endphp
            <div class="modal-backdrop" wire:click.self="closeNews" role="dialog" aria-modal="true">
                <div class="news-modal">
                    <div class="modal-top">
                        <span>{{ $n['day_label'] }} · جزئیات خبر</span>
                        <button type="button" class="modal-close" wire:click="closeNews" aria-label="بستن">×</button>
                    </div>
                    <h2>{{ $n['title'] }}</h2>
                    <p class="modal-summary">{{ $n['summary'] }}</p>
                    <div class="modal-tags">
                        <span class="qtag time">{{ $n['time'] }}</span>
                        <span class="qtag">{{ $n['category'] }}</span>
                        <span class="qtag {{ $n['status_key'] }}">{{ $n['status'] }}</span>
                    </div>
                    <div class="modal-info">
                        <div class="info-cell"><small>زمان برنامه‌ریزی‌شده</small><b>{{ $n['time'] }}</b></div>
                        <div class="info-cell"><small>دسته‌بندی</small><b>{{ $n['category'] }}</b></div>
                        <div class="info-cell"><small>نویسنده / خبرنگار</small><b>{{ $n['author'] }}</b></div>
                        <div class="info-cell"><small>وضعیت تحریریه</small><b>{{ $n['status'] }}</b></div>
                    </div>
                    <div class="modal-section">
                        <h4>خلاصه و توضیحات خبر</h4>
                        <p>{{ $n['description'] }}</p>
                    </div>
                    <div class="modal-section">
                        <h4>یادداشت دبیر</h4>
                        <p>{{ $n['note'] }}</p>
                    </div>
                    <div class="modal-footer">
                        <a class="modal-btn primary" href="{{ $n['edit_url'] }}">مشاهده پرونده خبر</a>
                        <button type="button" class="modal-btn" wire:click="closeNews">بستن پنجره</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
