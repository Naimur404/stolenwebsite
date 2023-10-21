<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Country extends BaseModel
{
    protected $table = 'countries';

    protected $fillable = [
        'name',
        'nationality',
        'code',
        'order',
        'is_default',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'nationality' => SafeContent::class,
        'code' => SafeContent::class,
    ];

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (Country $country) {
            $country->states()->delete();
            $country->cities()->delete();
        });
    }
}
