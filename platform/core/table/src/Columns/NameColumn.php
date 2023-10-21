<?php

namespace Botble\Table\Columns;

class NameColumn extends LinkableColumn
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'name', $name)
            ->title(trans('core/base::tables.name'))
            ->alignLeft();
    }
}
