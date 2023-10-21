<?php

namespace Botble\Table\Columns;

use Yajra\DataTables\Html\Column as BaseColumn;

class Column extends BaseColumn
{
    public function alignLeft(): static
    {
        return $this->addClass('text-start');
    }

    public function alignCenter(): static
    {
        return $this->addClass('text-center');
    }
}
