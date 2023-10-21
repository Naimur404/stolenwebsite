<?php

namespace Botble\Table\Columns;

class RowActionsColumn extends Column
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'row_actions', $name)
            ->title(trans('core/base::tables.operations'))
            ->alignCenter()
            ->orderable(false)
            ->searchable(false)
            ->exportable(false)
            ->printable(false)
            ->responsivePriority(99);
    }
}
