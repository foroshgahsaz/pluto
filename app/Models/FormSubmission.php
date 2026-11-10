<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToSite;
use Database\Factories\FormSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    /** @use HasFactory<FormSubmissionFactory> */
    use BelongsToSite, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'form_id',
        'site_id',
        'user_id',
        'payload_json',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(FormSubmissionFile::class, 'submission_id');
    }
}
