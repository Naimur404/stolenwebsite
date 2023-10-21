<?php

namespace Botble\Table\Columns;

class EnumColumn extends Column
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data, $name)
            ->alignCenter()
            ->width(100);
    }
}
