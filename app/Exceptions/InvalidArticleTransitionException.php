<?php

namespace App\Exceptions;

use App\Enums\ArticleStatus;
use RuntimeException;

class InvalidArticleTransitionException extends RuntimeException
{
    public static function make(ArticleStatus $from, ArticleStatus $to): self
    {
        return new self("انتقال وضعیت از {$from->label()} به {$to->label()} مجاز نیست.");
    }
}
