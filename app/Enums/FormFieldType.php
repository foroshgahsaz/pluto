<?php

namespace App\Enums;

enum FormFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Date = 'date';
    case Select = 'select';
    case MultiSelect = 'multiselect';
    case File = 'file';
    case Image = 'image';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'متن کوتاه',
            self::Textarea => 'متن بلند',
            self::Number => 'عدد',
            self::Date => 'تاریخ',
            self::Select => 'انتخابی',
            self::MultiSelect => 'چند انتخابی',
            self::File => 'آپلود فایل',
            self::Image => 'آپلود تصویر',
        };
    }

    public function isUpload(): bool
    {
        return in_array($this, [self::File, self::Image], true);
    }

    /**
     * @return list<string>
     */
    public function defaultRules(bool $required = false): array
    {
        $rules = match ($this) {
            self::Text => ['string', 'max:255'],
            self::Textarea => ['string', 'max:5000'],
            self::Number => ['numeric'],
            self::Date => ['date'],
            self::Select => ['string'],
            self::MultiSelect => ['array'],
            self::File => ['file'],
            self::Image => ['image'],
        };

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }
}
