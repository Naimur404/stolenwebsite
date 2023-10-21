<?php

namespace Botble\Payment\Services\Traits;

use Botble\Payment\Supports\PaymentHelper;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

trait PaymentErrorTrait
{
    protected ?string $errorMessage = null;

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $message = null): void
    {
        $this->errorMessage = $message;
    }

    protected function setErrorMessageAndLogging(Exception $exception, int $case): void
    {
        try {
            $error = [];

            if (! $exception instanceof ApiErrorException) {
                $this->errorMessage = $exception->getMessage();
            } else {
                $body = $exception->getJsonBody();
                $error = $body['error'];
                if (! empty($error['message'])) {
                    $this->errorMessage = $error['message'];
                } else {
                    $this->errorMessage = $exception->getMessage();
                }
            }

            Log::error(
                'Failed to make a payment charge.',
                PaymentHelper::formatLog([
                    'catch_case' => $case,
                    'http_status' => ($exception instanceof ApiErrorException) ? $exception->getHttpStatus() : 'not-have-http-status',
                    'error_type' => Arr::get($error, 'type', 'not-have-error-type'),
                    'error_code' => Arr::get($error, 'code', $exception->getCode()),
                    'error_param' => Arr::get($error, 'param', 'not-have-error-param'),
                    'error_message' => $this->errorMessage,
                ], __LINE__, __FUNCTION__, __CLASS__)
            );
        } catch (Exception $exception) {
            Log::error(
                'Failed to make a payment charge.',
                PaymentHelper::formatLog([
                    'catch_case' => $case,
                    'error_message' => $exception->getMessage(),
                ], __LINE__, __FUNCTION__, __CLASS__)
            );
        }
    }
}
