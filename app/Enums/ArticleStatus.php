<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Rejected = 'rejected';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::InReview => 'در حال بررسی',
            self::Rejected => 'رد شده',
            self::Approved => 'تأیید شده',
            self::Scheduled => 'زمان‌بندی شده',
            self::Published => 'منتشر شده',
            self::Archived => 'بایگانی',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::InReview],
            self::InReview => [self::Approved, self::Rejected],
            self::Rejected => [self::Draft],
            self::Approved => [self::Published, self::Scheduled, self::Draft],
            self::Scheduled => [self::Published, self::Approved],
            self::Published => [self::Archived, self::Draft],
            self::Archived => [self::Draft],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
