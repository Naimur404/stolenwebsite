<?php

namespace Botble\Table\Columns;

class StatusColumn extends EnumColumn
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'status', $name)
            ->title(trans('core/base::tables.status'));
    }
}
