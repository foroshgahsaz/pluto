<x-filament-panels::page>
    <div class="space-y-8">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            این صفحه فقط برای توسعه‌دهنده (سوپر ادمین) است و آخرین خطاهای پشتیبان‌گیری، صف‌های ناموفق و لاگ برنامه را نشان می‌دهد.
        </p>

        <section class="space-y-3">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">خطاهای پشتیبان‌گیری</h2>
            @if (count($failedBackups) === 0)
                <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                    خطای پشتیبان‌گیری ثبت‌شده‌ای وجود ندارد.
                </p>
            @else
                <div class="overflow-hidden rounded-xl border border-rose-200 dark:border-rose-900">
                    <table class="w-full text-sm">
                        <thead class="bg-rose-50 text-right dark:bg-rose-950">
                            <tr>
                                <th class="px-3 py-2 font-medium">نوع</th>
                                <th class="px-3 py-2 font-medium">زمان</th>
                                <th class="px-3 py-2 font-medium">پیام خطا</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100 dark:divide-rose-900">
                            @foreach ($failedBackups as $backup)
                                <tr class="bg-white dark:bg-gray-900">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $this->typeLabel($backup->type ?? 'full') }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ optional($backup->finished_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-rose-700 dark:text-rose-300 break-words">{{ $backup->message }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="space-y-3">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">جاب‌های ناموفق صف</h2>
            @if (count($failedJobs) === 0)
                <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                    جاب ناموفقی در صف وجود ندارد.
                </p>
            @else
                <div class="space-y-3">
                    @foreach ($failedJobs as $job)
                        <article class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm dark:border-amber-900 dark:bg-amber-950">
                            <div class="mb-2 flex flex-wrap gap-3 text-amber-900 dark:text-amber-100">
                                <span>{{ $job->uuid ?? ('#'.$job->id) }}</span>
                                <span>{{ $job->failed_at ?? '—' }}</span>
                                <span class="font-mono text-xs">{{ class_basename((string) ($job->payload ? (json_decode($job->payload, true)['displayName'] ?? 'job') : 'job')) }}</span>
                            </div>
                            <pre class="overflow-x-auto whitespace-pre-wrap break-words text-xs text-amber-950 dark:text-amber-50">{{ \Illuminate\Support\Str::limit((string) $job->exception, 1500) }}</pre>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-3">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">خطاهای لاگ برنامه</h2>
            @if (count($logErrors) === 0)
                <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                    خطای جدیدی در laravel.log پیدا نشد (یا فایل در دسترس نیست).
                </p>
            @else
                <div class="space-y-3">
                    @foreach ($logErrors as $entry)
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-900">
                            <div class="mb-2 flex flex-wrap gap-3">
                                <span class="rounded bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-900 dark:text-rose-100">{{ $entry['level'] }}</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $entry['time'] ?? '—' }}</span>
                            </div>
                            <pre class="overflow-x-auto whitespace-pre-wrap break-words text-xs text-gray-800 dark:text-gray-100">{{ $entry['message'] }}</pre>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
