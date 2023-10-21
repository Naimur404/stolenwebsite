<?php

namespace Botble\SimpleSlider\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;

class SimpleSliderItem extends BaseModel
{
    protected $table = 'simple_slider_items';

    protected $fillable = [
        'title',
        'description',
        'link',
        'image',
        'order',
        'simple_slider_id',
    ];

    protected $casts = [
        'title' => SafeContent::class,
        'description' => SafeContent::class,
        'link' => SafeContent::class,
    ];
}
