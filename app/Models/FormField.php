<?php

namespace App\Models;

use App\Enums\FormFieldType;
use App\Support\Tenancy\BelongsToSite;
use Database\Factories\FormFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    /** @use HasFactory<FormFieldFactory> */
    use BelongsToSite, HasFactory;

    protected $fillable = [
        'form_id',
        'site_id',
        'step',
        'type',
        'name',
        'label',
        'help_text',
        'placeholder',
        'is_required',
        'validation_rules_json',
        'options_json',
        'visibility_rules_json',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => FormFieldType::class,
            'is_required' => 'boolean',
            'validation_rules_json' => 'array',
            'options_json' => 'array',
            'visibility_rules_json' => 'array',
            'step' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
