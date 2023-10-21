@if ($shipment->isCancelled)
    <div class="ui-layout__item mb20">
        <div class="ui-banner ui-banner--status-warning">
            <div class="ui-banner__ribbon">
                <svg class="svg-next-icon svg-next-icon-size-20">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Circle-Alert</title><path fill="currentColor" d="M19 10c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"></path><path d="M10 0C4.486 0 0 4.486 0 10s4.486 10 10 10 10-4.486 10-10S15.514 0 10 0zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-13c-.552 0-1 .447-1 1v4c0 .553.448 1 1 1s1-.447 1-1V6c0-.553-.448-1-1-1zm0 8c-.552 0-1 .447-1 1s.448 1 1 1 1-.447 1-1-.448-1-1-1z"></path></svg>
                </svg>
            </div>
            <div class="ui-banner__content">
                <h2 class="ui-banner__title">{{ trans('plugins/ecommerce::shipping.shipment_canceled') }}</h2>
                <div class="ws-nm">
                    {{ trans('plugins/ecommerce::shipping.at') }} <i>{{ BaseHelper::formatDate($shipment->updated_at, 'H:i d/m/Y') }}</i>
                </div>
            </div>
        </div>
    </div>
@endif
