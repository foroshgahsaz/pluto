<?php

namespace App\Models;

use Database\Factories\BackupRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BackupRecord extends Model
{
    /** @use HasFactory<BackupRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'status',
        'type',
        'disk',
        'backup_name',
        'path',
        'size_bytes',
        'message',
        'triggered_by',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'finished_at' => 'datetime',
        ];
    }

    public function triggerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function fileExists(): bool
    {
        return filled($this->disk) && filled($this->path) && Storage::disk($this->disk)->exists($this->path);
    }
}
