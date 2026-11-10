<div class="space-y-3 text-sm">
    <p><span class="font-medium">نوع:</span> {{ \App\Support\Backup\BackupRunContext::label($record->type ?? 'full') }}</p>
    <p><span class="font-medium">دیسک:</span> {{ $record->disk ?: '—' }}</p>
    <p><span class="font-medium">زمان:</span> {{ optional($record->finished_at)->format('Y-m-d H:i:s') ?: '—' }}</p>
    <pre class="overflow-x-auto whitespace-pre-wrap break-words rounded-lg bg-gray-100 p-3 text-xs text-rose-800 dark:bg-gray-900 dark:text-rose-200">{{ $record->message }}</pre>
</div>
