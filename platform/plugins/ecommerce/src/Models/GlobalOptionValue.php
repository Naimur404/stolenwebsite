<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalOptionValue extends BaseModel
{
    protected $table = 'ec_global_option_value';

    protected $fillable = [
        'option_id',
        'option_value',
        'affect_price',
        'affect_type',
        'order',
    ];

    protected $casts = [
        'affect_price' => 'float',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(GlobalOption::class, 'option_id');
    }
}
