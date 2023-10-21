<?php

namespace Botble\Table\Abstracts\Concerns;

trait HasPriority
{
    protected int $priority = 0;

    public function priority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
