<?php

namespace Botble\Faq\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class FaqCategory extends BaseModel
{
    protected $table = 'faq_categories';

    protected $fillable = [
        'name',
        'order',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'category_id');
    }
}
