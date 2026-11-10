<?php

namespace App\Services\Ops;

use App\Models\BackupRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class SystemErrorReader
{
    /**
     * @return Collection<int, BackupRecord>
     */
    public function recentFailedBackups(int $limit = 20): Collection
    {
        return BackupRecord::query()
            ->where('status', 'failed')
            ->latest('finished_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function recentFailedJobs(int $limit = 20): Collection
    {
        if (! $this->tableExists('failed_jobs')) {
            return collect();
        }

        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return list<array{time:?string, level:string, message:string}>
     */
    public function recentLogErrors(?int $limit = null): array
    {
        $limit ??= (int) config('pluto.backup.error_log_lines', 80);
        $path = storage_path('logs/laravel.log');

        if (! File::exists($path) || ! File::isReadable($path)) {
            return [];
        }

        try {
            $raw = $this->tailFile($path, max(200, $limit * 40));
        } catch (Throwable) {
            return [];
        }

        $entries = [];
        $chunks = preg_split('/(?=\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?\])/u', $raw) ?: [];

        foreach (array_reverse($chunks) as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }

            if (! preg_match('/\.(ERROR|CRITICAL|ALERT|EMERGENCY):/i', $chunk)) {
                continue;
            }

            preg_match('/^\[(?<time>[^\]]+)\]\s+(?<env>[^.]+)\.(?<level>\w+):\s*(?<message>.*)$/s', $chunk, $m);

            $entries[] = [
                'time' => $m['time'] ?? null,
                'level' => strtoupper($m['level'] ?? 'ERROR'),
                'message' => str($m['message'] ?? $chunk)->limit(1200)->toString(),
            ];

            if (count($entries) >= $limit) {
                break;
            }
        }

        return $entries;
    }

    protected function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    protected function tailFile(string $path, int $bytes): string
    {
        $size = File::size($path);
        $start = max(0, $size - $bytes);
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return '';
        }

        try {
            if ($start > 0) {
                fseek($handle, $start);
            }

            $data = stream_get_contents($handle) ?: '';
        } finally {
            fclose($handle);
        }

        return $data;
    }
}
