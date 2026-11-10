<x-filament-panels::page>
    <div class="space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            آرشیوها روی دیسک خصوصی ذخیره می‌شوند و از وب عمومی قابل دسترسی نیستند.
            بازیابی فقط از طریق CLI و حالت نگهداری انجام می‌شود.
        </p>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
