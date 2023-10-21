<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceItem extends BaseModel
{
    protected $table = 'ec_invoice_items';

    protected $fillable = [
        'invoice_id',
        'reference_type',
        'reference_id',
        'name',
        'description',
        'image',
        'qty',
        'price',
        'sub_total',
        'tax_amount',
        'discount_amount',
        'amount',
        'metadata',
        'options',
    ];

    protected $casts = [
        'sub_total' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'amount' => 'float',
        'metadata' => 'json',
        'paid_at' => 'datetime',
        'options' => 'json',
        'name' => SafeContent::class,
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
