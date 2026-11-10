<x-filament-panels::page>
    <div class="space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            آرشیوها روی دیسک خصوصی ذخیره می‌شوند و از وب عمومی قابل دسترسی نیستند.
            بازیابی فقط از طریق CLI و حالت نگهداری انجام می‌شود.
        </p>

        <p class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-100">
            {{ $this->scheduleSummary() }}
        </p>

        @php($failures = $this->recentFailures())
        @if (count($failures) > 0)
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900 dark:bg-rose-950">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-rose-900 dark:text-rose-100">آخرین خطاهای پشتیبان‌گیری</h2>
                    <a href="{{ \App\Filament\Pages\SystemErrors::getUrl() }}" class="text-xs font-medium text-rose-800 underline dark:text-rose-200">
                        مشاهده همه خطاها
                    </a>
                </div>
                <ul class="space-y-2 text-sm text-rose-800 dark:text-rose-100">
                    @foreach ($failures as $failure)
                        <li>
                            <span class="font-medium">{{ \App\Support\Backup\BackupRunContext::label($failure->type ?? 'full') }}</span>
                            —
                            {{ optional($failure->finished_at)->format('Y-m-d H:i') }}
                            —
                            {{ \Illuminate\Support\Str::limit((string) $failure->message, 160) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $this->table }}
    </div>
</x-filament-panels::page>
