<?php

namespace App\Support\Tenancy;

use App\Models\Site;
use RuntimeException;

class SiteContext
{
    protected ?Site $site = null;

    protected bool $bypassScope = false;

    public function set(?Site $site): void
    {
        $this->site = $site;
    }

    public function setId(?int $siteId): void
    {
        $this->site = $siteId ? Site::query()->find($siteId) : null;
    }

    public function get(): ?Site
    {
        return $this->site;
    }

    public function id(): ?int
    {
        return $this->site?->id;
    }

    public function clear(): void
    {
        $this->site = null;
    }

    public function isBypassingScope(): bool
    {
        return $this->bypassScope;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function withoutScope(callable $callback): mixed
    {
        $previous = $this->bypassScope;
        $this->bypassScope = true;

        try {
            return $callback();
        } finally {
            $this->bypassScope = $previous;
        }
    }

    public function requireId(): int
    {
        $id = $this->id();

        if ($id === null) {
            throw new RuntimeException('Site context is not set.');
        }

        return $id;
    }
}
