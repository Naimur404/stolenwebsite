<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class ProductLabel extends BaseModel
{
    protected $table = 'ec_product_labels';

    protected $fillable = [
        'name',
        'color',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];
}
