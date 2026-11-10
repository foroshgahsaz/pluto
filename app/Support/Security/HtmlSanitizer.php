<?php

namespace App\Support\Security;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    protected ?HTMLPurifier $purifier = null;

    public function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return $this->purifier()->purify($html);
    }

    protected function purifier(): HTMLPurifier
    {
        if ($this->purifier) {
            return $this->purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', implode(',', [
            'p', 'br', 'span[style]', 'div',
            'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'a[href|title|target|rel]',
            'blockquote', 'pre', 'code',
            'img[src|alt|title|width|height]',
            'table', 'thead', 'tbody', 'tr', 'th[colspan|rowspan]', 'td[colspan|rowspan]',
            'hr',
        ]));
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.Nofollow', true);
        $config->set('HTML.TargetBlank', true);
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
        ]);
        $config->set('Cache.SerializerPath', storage_path('app/htmlpurifier'));
        if (! is_dir(storage_path('app/htmlpurifier'))) {
            @mkdir(storage_path('app/htmlpurifier'), 0755, true);
        }

        return $this->purifier = new HTMLPurifier($config);
    }
}
