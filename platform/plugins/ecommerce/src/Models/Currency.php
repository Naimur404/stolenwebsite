<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;

class Currency extends BaseModel
{
    protected $table = 'ec_currencies';

    protected $fillable = [
        'title',
        'symbol',
        'is_prefix_symbol',
        'order',
        'decimals',
        'is_default',
        'exchange_rate',
    ];
}
