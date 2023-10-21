<?php

namespace Botble\Table\Columns;

class CreatedAtColumn extends DateColumn
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'created_at', $name)
            ->title(trans('core/base::tables.created_at'));
    }
}
