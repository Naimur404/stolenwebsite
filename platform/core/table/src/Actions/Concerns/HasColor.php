<?php

namespace Botble\Table\Actions\Concerns;

trait HasColor
{
    protected string $color = 'primary';

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getColor(): string
    {
        return match ($this->color) {
            'info' => 'btn-info',
            'warning' => 'btn-warning',
            'danger' => 'btn-danger',
            'secondary' => 'btn-secondary',
            default => 'btn-primary',
        };
    }
}
