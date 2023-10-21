<?php

namespace Botble\Table\Columns;

class UpdatedAtColumn extends DateColumn
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'updated_at', $name)
            ->title(trans('core/base::tables.updated_at'));
    }
}
