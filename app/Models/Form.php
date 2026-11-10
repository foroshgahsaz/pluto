<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToSite;
use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    /** @use HasFactory<FormFactory> */
    use BelongsToSite, HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'title',
        'slug',
        'description',
        'success_message',
        'is_active',
        'is_multi_step',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_multi_step' => 'boolean',
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('step')->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest('id');
    }
}
