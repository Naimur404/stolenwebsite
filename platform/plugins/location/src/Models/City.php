<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class City extends BaseModel
{
    use HasSlug;

    protected $table = 'cities';

    protected $fillable = [
        'name',
        'state_id',
        'country_id',
        'record_id',
        'slug',
        'image',
        'order',
        'is_default',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class)->withDefault();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class)->withDefault();
    }

    protected static function booted(): void
    {
        self::saving(function (self $model) {
            $model->slug = self::createSlug($model->slug ?: $model->name, $model->getKey());
        });
    }
}
