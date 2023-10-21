<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class ProductFile extends BaseModel
{
    protected $table = 'ec_product_files';

    protected $fillable = [
        'product_id',
        'url',
        'extras',
    ];

    protected $casts = [
        'extras' => 'json',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function fileName(): Attribute
    {
        return Attribute::make(
            get: fn () => Arr::get($this->extras, 'name', '')
        );
    }

    public function fileSize(): Attribute
    {
        return Attribute::make(
            get: fn () => Arr::get($this->extras, 'size', '')
        );
    }

    public function mimeType(): Attribute
    {
        return Attribute::make(
            get: fn () => Arr::get($this->extras, 'mime_type', '')
        );
    }

    public function fileExtension(): Attribute
    {
        return Attribute::make(
            get: fn () => Arr::get($this->extras, 'extension', '')
        );
    }

    public function basename(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_name . ($this->file_extension ? '.' . $this->file_extension : '')
        );
    }

    public function isExternalLink(): Attribute
    {
        return Attribute::make(
            get: fn () => Arr::get($this->extras, 'is_external', false)
        );
    }
}
