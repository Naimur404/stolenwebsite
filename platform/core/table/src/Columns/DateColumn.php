<?php

namespace Botble\Table\Columns;

class DateColumn extends Column
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data, $name)
            ->type('date')
            ->width(100);
    }
}
