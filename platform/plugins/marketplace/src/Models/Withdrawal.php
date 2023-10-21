<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Enums\WithdrawalStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;

class Withdrawal extends BaseModel
{
    protected $table = 'mp_customer_withdrawals';

    protected $fillable = [
        'customer_id',
        'fee',
        'amount',
        'current_balance',
        'currency',
        'description',
        'payment_channel',
        'user_id',
        'status',
        'images',
        'bank_info',
        'transaction_id',
    ];

    protected $casts = [
        'status' => WithdrawalStatusEnum::class,
        'images' => 'array',
        'bank_info' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (&$withdrawal) {
            if ($withdrawal->id) {
                $statusOriginal = $withdrawal->getOriginal('status')->getValue();
                $status = $withdrawal->{$withdrawal->getTable() . '.status'} ?: $withdrawal->status->getValue();

                if (in_array($statusOriginal, [
                    WithdrawalStatusEnum::CANCELED,
                    WithdrawalStatusEnum::REFUSED,
                    WithdrawalStatusEnum::COMPLETED,
                ])) {
                    $withdrawal->status = $statusOriginal;
                    $withdrawal->{$withdrawal->getTable() . '.status'} = $statusOriginal;

                    return $withdrawal;
                }

                if (in_array($statusOriginal, [WithdrawalStatusEnum::PROCESSING, WithdrawalStatusEnum::PENDING]) &&
                    in_array($status, [WithdrawalStatusEnum::CANCELED, WithdrawalStatusEnum::REFUSED])) {
                    $vendor = $withdrawal->customer;

                    if ($vendor && $vendor->id) {
                        $vendorInfo = $vendor->vendorInfo;
                        if ($vendorInfo && $vendorInfo->id) {
                            $vendorInfo->balance += ($withdrawal->amount + $withdrawal->fee);
                            $vendorInfo->save();
                        }
                    }
                }
            }

            return $withdrawal;
        });

        static::deleting(function (Withdrawal $withdrawal) {
            if (in_array($withdrawal->status, [WithdrawalStatusEnum::PROCESSING, WithdrawalStatusEnum::PENDING])) {
                $vendor = $withdrawal->customer;

                if ($vendor && $vendor->id) {
                    $vendorInfo = $vendor->vendorInfo;
                    if ($vendorInfo && $vendorInfo->id) {
                        $vendorInfo->balance += ($withdrawal->amount + $withdrawal->fee);
                        $vendorInfo->save();
                    }
                }
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withDefault();
    }

    public function getVendorCanEditAttribute(): bool
    {
        return $this->status->getValue() === WithdrawalStatusEnum::PENDING;
    }

    public function canEditStatus(): bool
    {
        return in_array($this->status->getValue(), [
            WithdrawalStatusEnum::PENDING,
            WithdrawalStatusEnum::PROCESSING,
        ]);
    }

    public function getNextStatuses(): array
    {
        return match ($this->status->getValue()) {
            WithdrawalStatusEnum::PENDING => Arr::except(
                WithdrawalStatusEnum::labels(),
                WithdrawalStatusEnum::COMPLETED
            ),
            WithdrawalStatusEnum::PROCESSING => Arr::except(
                WithdrawalStatusEnum::labels(),
                WithdrawalStatusEnum::PENDING
            ),
            default => [$this->status->getValue() => $this->status->label()],
        };
    }

    public function getStatusHelper(): ?string
    {
        $status = $this->status->getValue();
        $key = 'plugins/marketplace::withdrawal.forms.' . $status . '_status_helper';

        return Lang::has($key) ? trans($key) : null;
    }
}
