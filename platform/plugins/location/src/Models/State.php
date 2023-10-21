<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class State extends BaseModel
{
    use HasSlug;

    protected $table = 'states';

    protected $fillable = [
        'name',
        'abbreviation',
        'country_id',
        'slug',
        'image',
        'order',
        'is_default',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'abbreviation' => SafeContent::class,
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class)->withDefault();
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (State $state) {
            $state->cities()->delete();
        });

        self::saving(function (self $model) {
            $model->slug = self::createSlug($model->slug ?: $model->name, $model->getKey());
        });
    }
}
