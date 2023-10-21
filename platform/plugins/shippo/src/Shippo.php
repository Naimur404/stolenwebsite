<?php

namespace Botble\Shippo;

use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Shipment;
use Botble\Location\Facades\Location;
use Botble\Support\Services\Cache\Cache;
use Carbon\Carbon;
use Exception;
use Illuminate\Log\Logger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Shippo as GoShippo;
use Shippo_Error;
use Shippo_Rate;
use Shippo_Shipment;
use Shippo_Transaction;

class Shippo
{
    protected string|null $liveApiToken;

    protected string|null $testApiToken;

    protected string|null $labelFileType;

    protected Cache $cache;

    protected array $packageTypes = [];

    protected array $serviceLevels = [];

    protected bool $sandbox = true;

    public const MAX_DESCRIPTION_LENGTH = 45;

    protected string|null $currency;

    protected array $statuses;

    protected array $contentTypes;

    protected LoggerInterface $logger;

    protected bool $useCache = true;

    protected bool $logging = true;

    protected bool $insurance;

    protected bool $signature;

    protected bool $validateAddress;

    protected string|array|null $distanceUnit;

    protected string|array|null $massUnit;

    protected string|array|null $defaultTariff;

    protected array $origin;

    public function __construct()
    {
        $this->liveApiToken = setting('shipping_shippo_production_key');
        $this->testApiToken = setting('shipping_shippo_test_key');
        $this->labelFileType = 'PDF';
        $this->sandbox = setting('shipping_shippo_sandbox', 1) == 1;

        if ($this->sandbox) {
            GoShippo::setApiKey($this->testApiToken);
        } else {
            GoShippo::setApiKey($this->liveApiToken);
        }

        $this->currency = get_application_currency()->title;

        $this->statuses = [
            'PRE_TRANSIT' => __('Shipping Label Created'),
            'TRANSIT' => __('In Transit'),
            'DELIVERED' => __('Delivered'),
            'RETURNED' => __('Returned to Sender'),
            'FAILURE' => __('Exception'),
            'UNKNOWN' => __('Shipping Label Created'),
        ];

        $this->contentTypes = [
            'MERCHANDISE' => __('Merchandise'),
            'DOCUMENTS' => __('Documents'),
            'GIFT' => __('Gift'),
            'RETURN_MERCHANDISE' => __('Returned Goods'),
            'HUMANITARIAN_DONATION' => __('Humanitarian Donation'),
            'OTHER' => __('Other'),
        ];

        $this->insurance = false;
        $this->signature = false;
        $this->validateAddress = false;
        $this->defaultTariff = '';
        $this->origin = $this->mergeAddress(EcommerceHelper::getOriginAddress());

        $this->distanceUnit = ecommerce_width_height_unit();
        $this->massUnit = ecommerce_weight_unit();

        $this->packageTypes = config('plugins.shippo.general.package_types', []);
        $this->serviceLevels = config('plugins.shippo.general.service_levels', []);

        $this->useCache = setting('shipping_shippo_cache_response', 1);

        $this->logging = setting('shipping_shippo_logging', 1);

        $this->cache = new Cache(app('cache'), self::class);

        $this->logger = Log::channel('shippo');
    }

    public function getName(): string
    {
        return 'Shippo';
    }

    public function mergeAddress(array $address): array
    {
        return array_merge($address, [
            'street1' => Arr::get($address, 'address'),
            'street2' => Arr::get($address, 'address_2'),
            'zip' => Arr::get($address, 'zip_code'),
        ]);
    }

    public function validate(): array
    {
        $errors = [];

        $apiTokenName = trans('plugins/shippo::shippo.live_api_token');
        if ($this->sandbox) {
            $apiTokenName = trans('plugins/shippo::shippo.test_api_token');
        }

        if (! $this->getApiKey()) {
            $errors[] = trans('plugins/shippo::shippo.token_name_is_required', ['name' => $apiTokenName]);
        } elseif (! $this->validateActiveApiToken()) {
            $errors[] = trans('plugins/shippo::shippo.token_name_is_invalid', ['name' => $apiTokenName]);
        }

        return $errors;
    }

    protected function validateActiveApiToken(): bool
    {
        try {
            Shippo_Shipment::all([
                'object_created_lt' => Carbon::now()->format('Y-m-d'),
                'async' => false,
            ]);

            return true;
        } catch (Exception $ex) {
            report($ex);

            return false;
        }
    }

    public function getRates(array $params, bool $suggest = true): array
    {
        $this->log([__LINE__, 'getRates: ' . json_encode($params)]);

        $prepareParams = $this->getPrepareParams($params);
        $newResponse = $this->getCacheOrNewRates($prepareParams);

        if (! Arr::get($newResponse, 'shipment.rates', []) && $suggest && Arr::get($prepareParams, 'extra.COD', [])) {
            $suggestParams = $prepareParams;
            Arr::forget($suggestParams, 'extra.COD');

            $suggestResponse = $this->getCacheOrNewRates($suggestParams);
            if ($rates = Arr::get($suggestResponse, 'shipment.rates', [])) {
                foreach ($rates as &$rate) {
                    $rate['disabled'] = true;
                    $rate['error_message'] = __('Not available in COD payment option.');
                }

                Arr::set($newResponse, 'shipment.rates', $rates);
            }
        }

        return $newResponse;
    }

    public function getCacheOrNewRates(array $params): array
    {
        $cacheKey = $this->getCacheKey($params);
        $response = $this->getCacheValue($cacheKey);
        if (! $response) {
            if (! $params['address_from'] || ! $params['address_to']) {
                $this->log([__LINE__, 'Cannot detect address, ' . json_encode($params)]);
            } else {
                $requestParams = $this->getRatesParams($params);
                $response = $this->createShipment($requestParams);
            }
        } else {
            $this->log([__LINE__, 'Found previously returned rates, so return them']);
        }

        $this->log([__LINE__, print_r($response, true)]);

        return $this->getRatesResponse($response, $params);
    }

    public function createShipment(array $params)
    {
        $cacheKey = $this->getCacheKey($params);
        $response = $this->getCacheValue($cacheKey);
        if (! $response) {
            $params = array_merge([
                'async' => false,
                'mode' => $this->sandbox ? 'test' : 'production',
                'extra' => [
                    'is_return' => false,
                ],
            ], $params);

            try {
                $response = Shippo_Shipment::create($params)->__toArray(true);
                if (Arr::get($response, 'status') == 'SUCCESS') {
                    $rates = Arr::get($response, 'rates', []);

                    if ($rates) {
                        $rates = $this->ratesByCurrency($rates);

                        if (! $rates) {
                            $ratesResponse = Shippo_Shipment::get_shipping_rates([
                                'id' => Arr::get($response, 'object_id'),
                                'currency' => $this->currency,
                            ])->__toArray(true);

                            $rates = $this->ratesByCurrency($ratesResponse['results']);
                        }

                        Arr::set($response, 'rates', $rates);
                    }

                    $this->log([__LINE__, 'Cache shipment for the future']);
                    $this->setCacheValue($cacheKey, $response);
                }
            } catch (Exception $ex) {
                report($ex);
            }
        } else {
            $this->log([__LINE__, 'Found previously returned rates, so return them']);
        }

        return $response;
    }

    protected function ratesByCurrency(array $rates): array
    {
        $rates = collect($rates)
                ->filter(function ($rate) {
                    return in_array($this->currency, [$rate['currency'], $rate['currency_local']]);
                })
                ->toArray();

        $newRates = [];
        foreach ($rates as $key => $rate) {
            $newRates[$key] = $rate;

            if ($rate['currency'] == $this->currency) {
                $newRates[$key]['price'] = $rate['amount'];
            } elseif ($rate['currency_local'] == $this->currency) {
                $newRates[$key]['price'] = $rate['amount_local'];
            }
        }

        return $newRates;
    }

    public function log(array $logs): self
    {
        if ($this->logging) {
            /**
             * @var Logger $logger
             */
            $logger = $this->logger;
            $logger->debug($logs);
        }

        return $this;
    }

    public function getPrepareParams(array $inParams): array
    {
        $params['extra'] = Arr::get($inParams, 'extra', []);
        $params['address_from'] = $this->getRequestedOrigin($inParams);
        $params['address_from'] = $this->prepareAddress($params['address_from']);

        $params['address_to'] = [];
        if ($addressTo = Arr::get($inParams, 'address_to')) {
            $params['address_to'] = $this->prepareAddress($addressTo);
        }

        $params['parcels'] = $this->prepareParcelInfo($inParams);
        $params['items'] = Arr::get($inParams, 'items', []);

        return $params;
    }

    public function getCacheKey(array $params): string
    {
        $params['api'] = $this->getApiKey();

        $jsonData = json_encode($params);

        return md5($jsonData) . ($this->sandbox ? '_test' : '_production');
    }

    protected function getRatesParams(array $inParams): array
    {
        $params = [
            'async' => false,
            'mode' => $this->sandbox ? 'test' : 'production',
            'extra' => [
                'is_return' => false,
            ],
        ];

        if ($isReturn = Arr::get($inParams, 'extra.is_return')) {
            $params['extra']['is_return'] = (bool) $isReturn;
        }

        if ($orderId = Arr::get($inParams, 'extra.order_id')) {
            $params['extra']['reference_1'] = $orderId;
            $params['metadata'] = sprintf('Order %s', $orderId);
        }

        if ($orderToken = Arr::get($inParams, 'extra.order_token')) {
            $params['metadata'] = sprintf('Order Token %s', $orderToken);
        }

        if ($orderNumber = Arr::get($inParams, 'order_number')) {
            $params['extra']['reference_2'] = $orderNumber;
        }

        if ($this->isInsuranceRequested($inParams) && ! empty($inParams['value'])) {
            $params['extra']['insurance'] = [
                'amount' => $inParams['value'],
                'currency' => $this->currency,
            ];
        }

        if ($this->isSignatureRequested($inParams)) {
            $params['extra']['signature_confirmation'] = 'STANDARD';
        }

        if ($cod = Arr::get($inParams, 'extra.COD', [])) {
            $params['extra']['COD'] = $cod;
        }

        if ($addressFrom = Arr::get($inParams, 'address_from')) {
            $this->log([__LINE__, 'From Address: ' . json_encode($addressFrom)]);
            $params['address_from'] = $this->getCachedAddress($addressFrom);
        }

        if ($addressTo = Arr::get($inParams, 'address_to')) {
            $this->log([__LINE__, 'To Address: ' . json_encode($addressTo)]);
            $params['address_to'] = $this->getCachedAddress($addressTo);
        }

        $parcelsInfo = Arr::only($params, ['extra', 'metadata']) ?: [];
        $parcelsInfo['parcels'] = Arr::get($inParams, 'parcels') ?: [];

        $params['parcels'] = $this->getCachedParcelInfo($parcelsInfo);

        if (isset($inParams['address_from']['country'])
            && isset($inParams['address_to']['country'])
            && $inParams['address_from']['country'] != $inParams['address_to']['country']) {
            $params['customs_declaration'] = $this->getCachedCustomsInfo($inParams);
        }

        return $params;
    }

    protected function isInsuranceRequested(array $inParams): bool
    {
        $insurance = $this->insurance;
        if (isset($inParams['extra']['insurance'])) {
            $insurance = filter_var($inParams['extra']['insurance'], FILTER_VALIDATE_BOOLEAN);
        }

        return $insurance;
    }

    protected function isSignatureRequested(array $inParams): bool
    {
        $signature = $this->signature;
        if (isset($inParams['extra']['signature'])) {
            $signature = filter_var($inParams['extra']['signature'], FILTER_VALIDATE_BOOLEAN);
        }

        return $signature;
    }

    protected function getRequestedOrigin(array $inParams): array
    {
        $origin = Arr::get($inParams, 'origin');
        if (! $origin) {
            $origin = $this->origin;
        }

        return $this->mergeAddress($origin);
    }

    protected function getCachedParcelInfo(array $inParams)
    {
        $cacheKey = $this->getCacheKey($inParams);
        $parcelId = $this->getCacheValue($cacheKey);
        if (! empty($parcelId)) {
            $this->log([__LINE__, 'Found previous cached parcel ID: ' . $parcelId . ', so re-use it']);

            return $parcelId;
        }

        return $inParams['parcels'];
    }

    protected function prepareParcelInfo(array $inParams): array
    {
        $length = 0;
        $width = 0;
        $height = 0;

        foreach (Arr::get($inParams, 'items', []) as $item) {
            $_length = $item['length'] * $item['qty'];
            $_height = $item['height'] * $item['qty'];
            $length = max($length, $_length);
            $height = $height > $_height ? $length : $_height;
            $width += $item['wide'] * $item['qty'];
        }

        $parcel = [
            'weight' => round(EcommerceHelper::validateOrderWeight(Arr::get($inParams, 'weight', 0)), 2),
            'length' => round($length, 2),
            'width' => round($width, 2),
            'height' => round($height, 2),
            'distance_unit' => $this->distanceUnit,
            'mass_unit' => $this->massUnit,
        ];

        if (! empty($inParams['type']) && $inParams['type'] != 'parcel' && isset($this->packageTypes[$inParams['type']])) {
            $parcel['template'] = $inParams['type'];
        }

        return [$parcel];
    }

    public function getCacheValue($cacheKey)
    {
        if ($this->useCache) {
            return $this->cache->get($cacheKey);
        }

        return null;
    }

    public function setCacheValue($cacheKey, $value): bool
    {
        if ($cacheKey) {
            return $this->cache->put($cacheKey, $value);
        }

        return true;
    }

    protected function getCachedAddress($options)
    {
        $cacheKey = $this->getCacheKey($options);
        $addrId = $this->getCacheValue($cacheKey);
        if (! empty($addrId)) {
            $this->log([__LINE__, 'Found previous cached address ID: ' . $addrId . ', so re-use it']);

            return $addrId;
        }

        return $options;
    }

    protected function prepareAddress(array $options): array
    {
        $addr = $this->mergeAddress($options);

        $validator = Validator::make($addr, $this->getAddressFromValidationRules());

        if ($validator->fails()) {
            $this->log([__LINE__, 'Address is invalid ' . json_encode($addr)]);

            $this->log([__LINE__, $validator->getMessageBag()->first()]);

            return [];
        }

        return $this->afterPrepareAddress($addr);
    }

    protected function afterPrepareAddress(array $addr): array
    {
        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            $cityId = $addr['city'];
            $city = Location::getCityById($cityId);
            if ($city) {
                $addr['city'] = $city->name;
                $addr['state'] = $city->state->abbreviation;
                $addr['country'] = $city->state->country->code;
            }
        }

        return $addr;
    }

    protected function getAddressFromValidationRules(): array
    {
        return EcommerceHelper::getCustomerAddressValidationRules();
    }

    protected function getValidationErrors($addressField, $addressType): array
    {
        if (empty($addressField['validation_results']) || ! empty($addressField['validation_results']['is_valid'])) {
            return [];
        }

        $this->log([__LINE__, 'Address is invalid: ' . print_r($addressField['validation_results'], true)]);

        $validationErrors = [];

        foreach ($addressField['validation_results']['messages'] as $error) {
            $errorMessage = $this->getErrorMessage($error);
            $validationErrors[$addressType][] = $errorMessage;
        }

        return $validationErrors;
    }

    protected function getErrorMessage($error)
    {
        if (isset($error['object_id']) || isset($error['results']) || isset($error['tracking_number'])) {
            return '';
        }

        if (isset($error['__all__'])) {
            $error = $error['__all__'];
        }

        if (is_string($error)) {
            return $error;
        }

        if (isset($error['text'])) {
            return $error['text'];
        }

        $message = '';
        if (is_array($error)) {
            foreach ($error as $key => $val) {
                if (! empty($message)) {
                    $message .= "\n";
                }

                if (! is_numeric($key)) {
                    $message .= $key . ' -> ';
                }

                $message .= $this->getErrorMessage($val);
            }
        }

        return trim($message);
    }

    protected function getCachedCustomsInfo(array $inParams)
    {
        $customsInfo = $this->prepareCustomsInfo($inParams);

        $cacheKey = $this->getCacheKey($customsInfo);
        $customsInfoId = $this->getCacheValue($cacheKey);
        if (! empty($customsInfoId)) {
            $this->log([__LINE__, 'Found previous cached customs info ID: ' . $customsInfoId . ', so re-use it']);

            return $customsInfoId;
        }

        return $customsInfo;
    }

    protected function prepareCustomsInfo(array $inParams): array
    {
        $customsInfo = [
            'certify' => true,
            'non_delivery_option' => 'RETURN',
            'certify_signer' => trim(Arr::get($inParams, 'address_from.name') ?: Arr::get($inParams, 'address_from.company')) ?: 'Shipper',
            'contents_type' => 'MERCHANDISE',
        ];

        if (! empty($inParams['order_number'])) {
            $customsInfo['invoice'] = $inParams['order_number'];
        }

        if (! empty($inParams['contents']) && ! empty($this->contentTypes[$inParams['contents']])) {
            $customsInfo['contents_type'] = $inParams['contents'];
        }

        if (isset($inParams['description'])) {
            $customsInfo['contents_explanation'] = $inParams['description'];
        }

        $defaultOriginCountry = '';
        if (isset($inParams['address_from']['country'])) {
            $defaultOriginCountry = strtoupper($inParams['address_from']['country']);
        }

        if (! empty($inParams['items']) && is_array($inParams['items'])) {
            $customsInfo['items'] = $this->prepareCustomsItems($inParams['items'], $defaultOriginCountry);
        }

        $this->log([__LINE__, 'Customs Info: ' . print_r($customsInfo, true)]);

        return $customsInfo;
    }

    protected function prepareCustomsItems(array $itemsInParcel, $defaultOriginCountry): array
    {
        $customsItems = [];

        foreach ($itemsInParcel as $itemInParcel) {
            if (empty($itemInParcel['country'])) {
                $itemInParcel['country'] = $defaultOriginCountry;
            }

            $customsItem = $this->prepareCustomsItem($itemInParcel);
            if (! empty($customsItem)) {
                $customsItems[] = $customsItem;
            }
        }

        return $customsItems;
    }

    protected function prepareCustomsItem($itemInParcel): array
    {
        if (empty($itemInParcel['name']) ||
            ! isset($itemInParcel['weight']) ||
            empty($itemInParcel['qty']) ||
            ! isset($itemInParcel['price'])) {
            $this->log([__LINE__, 'Item is invalid, so skip it ' . print_r($itemInParcel, true)]);

            return [];
        }

        $value = $itemInParcel['price'] * $itemInParcel['qty'];

        $tariff = $this->defaultTariff;
        if (! empty($itemInParcel['tariff'])) {
            $tariff = $itemInParcel['tariff'];
        }

        $description = preg_replace('/[^\w\d\s]/', '?', utf8_decode($itemInParcel['name']));

        return [
            'description' => Str::limit($description, self::MAX_DESCRIPTION_LENGTH),
            'quantity' => $itemInParcel['qty'],
            'value_amount' => round($value, 3),
            'value_currency' => $this->currency,
            'net_weight' => round(EcommerceHelper::validateOrderWeight($itemInParcel['weight']), 3),
            'mass_unit' => $this->massUnit,
            'origin_country' => $itemInParcel['country'],
            'tariff_number' => $tariff,
        ];
    }

    protected function getShipmentResponse($response, array $params): array
    {
        if (empty($response['object_id'])) {
            $this->log([__LINE__, 'Shipment ID has not been found']);

            return [];
        }

        if ($addressFrom = Arr::get($response, 'address_from')) {
            $validationErrors = $this->getValidationErrors($addressFrom, 'origin');
            if (! empty($validationErrors)) {
                $response['address_from']['object_id'] = null;
            }
        }

        if ($addressTo = Arr::get($response, 'address_to')) {
            if ($this->validateAddress && empty($addressTo['is_complete'])) {
                $validationErrors['destination'][] = __('Address appears to be incomplete');

                $this->log([__LINE__, 'Address is incomplete']);
            }

            $validationErrors = $this->getValidationErrors($addressTo, 'destination');
            if (! empty($validationErrors)) {
                $response['address_to']['object_id'] = null;
            }
        }

        $shipmentId = $this->getShipmentId($response, $params);

        $rates = [];
        foreach (Arr::get($response, 'rates', []) as $rate) {
            $serviceId = $rate['servicelevel']['token'];
            $serviceName = $rate['servicelevel']['name'];

            if (Arr::has($this->serviceLevels, $serviceId)) {
                $serviceName = $this->serviceLevels[$serviceId];
            }

            $days = Arr::get($rate, 'days', Arr::get($rate, 'estimated_days', 0));
            if ($days) {
                $description = trans('plugins/shippo::shippo.estimated_days', ['day' => $days]);
            } else {
                $description = Arr::get($rate, 'duration_terms', '');
            }

            $rates[$serviceId] = [
                'id' => $rate['object_id'],
                'service' => $serviceId,
                'carrier' => $rate['provider'],
                'name' => $serviceName,
                'delivery_days' => $days,
                'description' => $description,
                'price' => Arr::get($rate, 'price'),
                'image' => $rate['provider_image_75'],
                'company_name' => $serviceName,
                'shipment_id' => $shipmentId,
            ];
        }

        $newResponse = [
            'shipment' => [
                'id' => $shipmentId,
                'rates' => $this->sortRates($rates),
            ],
        ];

        if (! empty($validationErrors)) {
            $newResponse['validation_errors'] = $validationErrors;
        }

        return $newResponse;
    }

    public function sortRates(array $rates): array
    {
        uasort($rates, function ($rate1, $rate2) {
            return $rate1['price'] > $rate2['price'] ? 1 : -1;
        });

        return $rates;
    }

    protected function getRatesResponse($response, array $params): array
    {
        $newResponse = $this->getShipmentResponse($response, $params);

        if (Arr::get($newResponse, 'shipment.id')) {
            $this->setShipmentCacheValues($response, $params);
        }

        return $newResponse;
    }

    protected function setShipmentCacheValues($response, array $params): void
    {
        if (($addressFrom = Arr::get($response, 'address_from')) && $this->isResponseObjectValid($addressFrom)) {
            $addrId = $addressFrom['object_id'];
            $this->log([__LINE__, 'Cache from address ID: ' . $addrId]);

            $cacheKey = $this->getCacheKey(Arr::get($params, 'address_from'));
            $this->setCacheValue($cacheKey, $addrId);
        }

        if (($addressTo = Arr::get($response, 'address_to')) && $this->isResponseObjectValid($addressTo)) {
            $addrId = $addressTo['object_id'];
            $this->log([__LINE__, 'Cache to address ID: ' . $addrId]);

            $cacheKey = $this->getCacheKey(Arr::get($params, 'address_to'));
            $this->setCacheValue($cacheKey, $addrId);
        }

        if (($parcel = Arr::get($response, 'parcels.0')) && $this->isResponseObjectValid($parcel)) {
            $parcelId = $parcel['object_id'];
            $this->log([__LINE__, 'Cache parcel ID: ' . $parcelId]);

            $cacheKey = $this->getCacheKey(Arr::get($params, 'parcels.0'));

            $this->setCacheValue($cacheKey, $parcelId);
        }
    }

    protected function isResponseObjectValid($object): bool
    {
        $isValid = false;

        if (! empty($object['object_id']) && ! empty($object['object_state']) && $object['object_state'] == 'VALID') {
            $isValid = true;
        }

        return $isValid;
    }

    protected function getShipmentId($response, array $params = []): string
    {
        $shipmentId = '';

        // 1. shipment id
        if (! empty($params['shipment_id'])) {
            $shipmentId = $params['shipment_id'];
        } elseif (! empty($response['shipment_id'])) {
            $shipmentId = $response['shipment_id'];
        } elseif (! empty($response['object_id'])) {
            $shipmentId = $response['object_id'];
        }

        return $shipmentId;
    }

    protected function getApiKey()
    {
        return $this->sandbox ? $this->testApiToken : $this->liveApiToken;
    }

    public function canCreateTransaction(Shipment $shipment): bool
    {
        $order = $shipment->order;
        if ($order && $order->id && $order->shipping_method->getValue() == SHIPPO_SHIPPING_METHOD_NAME
            && in_array($shipment->status->getValue(), [ShippingStatusEnum::APPROVED, ShippingStatusEnum::PENDING])) {
            return true;
        }

        return false;
    }

    public function createTransaction(string $rateId): array
    {
        try {
            $transaction = Shippo_Transaction::create([
                'rate' => $rateId,
                'async' => false,
            ])->__toArray(true);

            $this->log([__LINE__, $transaction]);

            return $transaction;
        } catch (Exception $ex) {
            report($ex);

            return [];
        }
    }

    public function retrieveRate(string $rateId)
    {
        $cacheKey = $this->getCacheKey(['function' => __FUNCTION__, 'rate_id' => $rateId]);
        $response = $this->getCacheValue($cacheKey);
        if (! $response) {
            $response = Shippo_Rate::retrieve($rateId)->__toArray(true);
            $this->setCacheValue($cacheKey, $response);
        }

        return $response;
    }

    public function retrieveShipment(string $shipmentId)
    {
        $cacheKey = $this->getCacheKey(['function' => __FUNCTION__, 'shipment_id' => $shipmentId]);
        $response = $this->getCacheValue($cacheKey);
        if (! $response) {
            try {
                $response = Shippo_Shipment::retrieve($shipmentId)->__toArray(true);
                $this->setCacheValue($cacheKey, $response);
            } catch (Shippo_Error $ex) {
                $response['status'] = 'ERROR';
                $response['message'] = $ex->getMessage();

                $this->log([__LINE__, $ex->getMessage()]);
            } catch (Exception $ex) {
                $this->log([__LINE__, $ex->getMessage()]);
            }
        }

        return $response;
    }
}
