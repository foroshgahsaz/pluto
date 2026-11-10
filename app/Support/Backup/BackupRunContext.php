<?php

namespace App\Support\Backup;

class BackupRunContext
{
    public const TYPE_DATABASE = 'database';

    public const TYPE_FILES = 'files';

    public const TYPE_FULL = 'full';

    protected static ?string $type = null;

    public static function set(?string $type): void
    {
        self::$type = $type;
    }

    public static function get(): string
    {
        return self::$type ?? self::TYPE_FULL;
    }

    public static function clear(): void
    {
        self::$type = null;
    }

    public static function label(string $type): string
    {
        return match ($type) {
            self::TYPE_DATABASE => 'دیتابیس',
            self::TYPE_FILES => 'فایل‌ها',
            default => 'کامل',
        };
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [self::TYPE_DATABASE, self::TYPE_FILES, self::TYPE_FULL];
    }
}
