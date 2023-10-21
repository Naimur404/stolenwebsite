<?php

namespace Botble\Table\Columns;

class IdColumn extends Column
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'id', $name)
            ->title(trans('core/base::tables.id'))
            ->alignCenter()
            ->width(20);
    }
}
