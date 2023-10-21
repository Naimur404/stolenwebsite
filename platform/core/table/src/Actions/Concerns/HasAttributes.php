<?php

namespace Botble\Table\Actions\Concerns;

use Botble\Base\Facades\Html;
use Illuminate\Support\Arr;

trait HasAttributes
{
    protected array $attributes = [];

    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributes(): string
    {
        return Html::attributes($this->attributes);
    }

    public function getAttribute(string $attribute, string $default = null)
    {
        return Arr::get($this->attributes, $attribute, $default);
    }
}
