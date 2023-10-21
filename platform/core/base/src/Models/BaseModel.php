<?php

namespace Botble\Base\Models;

use Botble\Base\Contracts\BaseModel as BaseModelContract;
use Botble\Base\Facades\MacroableModels;
use Botble\Base\Models\Concerns\HasBaseEloquentBuilder;
use Botble\Base\Models\Concerns\HasMetadata;
use Botble\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class BaseModel extends Model implements BaseModelContract
{
    use HasBaseEloquentBuilder;
    use HasMetadata;
    use HasUuidsOrIntegerIds;

    public function __get($key)
    {
        if (MacroableModels::modelHasMacro($this::class, $method = 'get' . Str::studly($key) . 'Attribute')) {
            return call_user_func([$this, $method]);
        }

        return parent::__get($key);
    }
}
