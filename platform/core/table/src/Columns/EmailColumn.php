<?php

namespace Botble\Table\Columns;

class EmailColumn extends Column
{
    protected bool $linkable = false;

    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'email', $name)
            ->title(trans('core/base::tables.email'))
            ->alignLeft();
    }

    public function linkable(bool $linkable = true): static
    {
        $this->linkable = $linkable;

        return $this;
    }

    public function isLinkable(): bool
    {
        return $this->linkable;
    }
}
