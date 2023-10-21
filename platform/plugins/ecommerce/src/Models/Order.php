<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Order extends BaseModel
{
    protected $table = 'ec_orders';

    protected $fillable = [
        'status',
        'user_id',
        'amount',
        'tax_amount',
        'shipping_method',
        'shipping_option',
        'shipping_amount',
        'description',
        'coupon_code',
        'discount_amount',
        'sub_total',
        'is_confirmed',
        'discount_description',
        'is_finished',
        'token',
        'completed_at',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'shipping_method' => ShippingMethodEnum::class,
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        self::deleting(function (Order $order) {
            $order->shipment()->delete();
            $order->histories()->delete();
            $order->products()->delete();
            $order->address()->delete();
            $order->invoice()->delete();

            if (is_plugin_active('payment')) {
                $order->payment()->delete();
            }
        });

        static::creating(function (Order $order) {
            $order->code = static::generateUniqueCode();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id', 'id')->withDefault();
    }

    protected function userName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user->name
        );
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->shippingAddress->full_address
        );
    }

    protected function shippingMethodName(): Attribute
    {
        return Attribute::make(
            get: fn () => OrderHelper::getShippingMethod(
                $this->attributes['shipping_method'],
                $this->attributes['shipping_option']
            )
        );
    }

    public function address(): HasOne
    {
        return $this->hasOne(OrderAddress::class, 'order_id')
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->withDefault();
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class, 'order_id')
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->withDefault();
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class, 'order_id')
            ->where('type', OrderAddressTypeEnum::BILLING)
            ->withDefault();
    }

    public function referral(): HasOne
    {
        return $this->hasOne(OrderReferral::class, 'order_id')->withDefault();
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_id')->with(['product']);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class, 'order_id')->with(['user', 'order']);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class)->withDefault();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id')->withDefault();
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'reference_id')->withDefault();
    }

    public function taxInformation(): HasOne
    {
        return $this->hasOne(OrderTaxInformation::class, 'order_id');
    }

    public function canBeCanceled(): bool
    {
        if ($this->shipment && in_array(
            $this->shipment->status,
            [ShippingStatusEnum::PICKED, ShippingStatusEnum::DELIVERED, ShippingStatusEnum::AUDITED]
        )) {
            return false;
        }

        return in_array($this->status, [OrderStatusEnum::PENDING, OrderStatusEnum::PROCESSING]);
    }

    public function canBeCanceledByAdmin(): bool
    {
        if ($this->shipment && in_array(
            $this->shipment->status,
            [ShippingStatusEnum::DELIVERED, ShippingStatusEnum::AUDITED]
        )) {
            return false;
        }

        if (in_array($this->status, [OrderStatusEnum::COMPLETED, OrderStatusEnum::CANCELED])) {
            return false;
        }

        if ($this->shipment && in_array($this->shipment->status, [
                ShippingStatusEnum::PENDING,
                ShippingStatusEnum::APPROVED,
                ShippingStatusEnum::NOT_APPROVED,
                ShippingStatusEnum::ARRANGE_SHIPMENT,
                ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT,
            ])) {
            return true;
        }

        return true;
    }

    public function getIsFreeShippingAttribute(): bool
    {
        return $this->shipping_amount == 0 && $this->discount_amount == 0 && $this->coupon_code;
    }

    public function getAmountFormatAttribute(): string
    {
        return format_price($this->amount);
    }

    public function getDiscountAmountFormatAttribute(): string
    {
        return format_price($this->shipping_amount);
    }

    public function isInvoiceAvailable(): bool
    {
        return $this->invoice()->exists() && (! EcommerceHelper::disableOrderInvoiceUntilOrderConfirmed(
        ) || $this->is_confirmed);
    }

    public function getProductsWeightAttribute(): float|int
    {
        $weight = 0;

        foreach ($this->products as $product) {
            if ($product && $product->weight) {
                $weight += $product->weight * $product->qty;
            }
        }

        return EcommerceHelper::validateOrderWeight($weight);
    }

    public function returnRequest(): HasOne
    {
        return $this->hasOne(OrderReturn::class, 'order_id')->withDefault();
    }

    public function canBeReturned(): bool
    {
        if (! EcommerceHelper::isOrderReturnEnabled()) {
            return false;
        }

        if ($this->status != OrderStatusEnum::COMPLETED || ! $this->completed_at) {
            return false;
        }

        $shipmentDayCount = Carbon::now()->diffInDays($this->completed_at);

        if ($shipmentDayCount > EcommerceHelper::getReturnableDays()) {
            return false;
        }

        if (EcommerceHelper::isEnabledSupportDigitalProducts()) {
            if ($this->products->where('times_downloaded')->count()) {
                return false;
            }
        }

        return ! $this->returnRequest()->exists();
    }

    public static function generateUniqueCode(): string
    {
        $nextInsertId = BaseModel::determineIfUsingUuidsForId() ? static::query()->count() + 1 : static::query()->max(
            'id'
        ) + 1;

        do {
            $code = get_order_code($nextInsertId);
            $nextInsertId++;
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function digitalProducts(): Collection
    {
        return $this->products->filter(fn ($item) => $item->isTypeDigital());
    }

    public static function countRevenueByDateRange(CarbonInterface $startDate, CarbonInterface $endDate): float
    {
        return self::query()
            ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->whereDate('payments.created_at', '>=', $startDate)
            ->whereDate('payments.created_at', '<=', $endDate)
            ->where('payments.status', PaymentStatusEnum::COMPLETED)
            ->sum(DB::raw('COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)'));
    }

    public static function getRevenueData(
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        $select = []
    ): Collection {
        if (empty($select)) {
            $select = [
                DB::raw('DATE(payments.created_at) AS date'),
                DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
            ];
        }

        return self::query()
            ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->whereDate('payments.created_at', '>=', $startDate)
            ->whereDate('payments.created_at', '<=', $endDate)
            ->where('payments.status', PaymentStatusEnum::COMPLETED)
            ->groupBy('date')
            ->select($select)
            ->get();
    }
}
