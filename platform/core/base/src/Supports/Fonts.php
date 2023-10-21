<?php

namespace Botble\Base\Supports;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class Fonts implements Htmlable
{
    public function __construct(
        protected string $googleFontsUrl,
        protected string|null $localizedUrl = null,
        protected string|null $localizedCss = null,
        protected string|null $nonce = null,
        protected bool $preferInline = false,
    ) {
    }

    public function inline(): HtmlString
    {
        if (! $this->localizedCss) {
            return $this->fallback();
        }

        $attributes = $this->parseAttributes([
            'nonce' => $this->nonce ?? false,
        ]);

        $this->localizedCss = preg_replace('!/\*.*?\*/!s', '', $this->localizedCss);

        return new HtmlString(
            <<<HTML
            <style {$attributes->implode(' ')}>{$this->localizedCss}</style>
        HTML
        );
    }

    public function link(): HtmlString
    {
        if (! $this->localizedUrl) {
            return $this->fallback();
        }

        $attributes = $this->parseAttributes([
            'href' => $this->localizedUrl,
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'nonce' => $this->nonce ?? false,
        ]);

        return new HtmlString(
            <<<HTML
            <link {$attributes->implode(' ')}>
        HTML
        );
    }

    public function fallback(): HtmlString
    {
        $attributes = $this->parseAttributes([
            'href' => $this->googleFontsUrl,
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'nonce' => $this->nonce ?? false,
        ]);

        return new HtmlString(
            <<<HTML
            <link {$attributes->implode(' ')}>
        HTML
        );
    }

    public function url(): string
    {
        if (! $this->localizedUrl) {
            return $this->googleFontsUrl;
        }

        return $this->localizedUrl;
    }

    public function toHtml()
    {
        return $this->preferInline ? $this->inline() : $this->link();
    }

    protected function parseAttributes($attributes): Collection
    {
        return Collection::make($attributes)
            ->reject(fn ($value, $key) => in_array($value, [false, null], true))
            ->flatMap(fn ($value, $key) => $value === true ? [$key] : [$key => $value])
            ->map(fn ($value, $key) => is_int($key) ? $value : $key . '="' . $value . '"')
            ->values();
    }
}
