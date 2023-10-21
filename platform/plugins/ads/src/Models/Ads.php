<?php

namespace Botble\Ads\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Ads extends BaseModel
{
    protected $table = 'ads';

    protected $fillable = [
        'name',
        'key',
        'status',
        'open_in_new_tab',
        'expired_at',
        'location',
        'image',
        'url',
        'clicked',
        'order',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'expired_at' => 'date',
        'open_in_new_tab' => 'boolean',
    ];

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->whereDate('expired_at', '>=', Carbon::now());
    }
}
