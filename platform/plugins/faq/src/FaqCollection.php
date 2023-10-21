<?php

namespace Botble\Faq;

use Illuminate\Contracts\Support\Arrayable;

class FaqCollection implements Arrayable
{
    protected array $items = [];

    public function push(FaqItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
