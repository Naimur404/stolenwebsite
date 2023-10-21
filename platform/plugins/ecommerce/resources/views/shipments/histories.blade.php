@if ($shipment->histories->count())
    <div class="mt20 mb20 timeline-shipment">
        <div class="comment-log ws-nm">
            <div class="comment-log-title">
                <label class="bold-light m-xs-b hide-print">{{ trans('plugins/ecommerce::shipping.history') }}</label>
            </div>
            <div class="comment-log-timeline">
                <div class="column-left-history ps-relative" id="order-history-wrapper">
                    @foreach ($shipment->histories as $history)
                        <div class="item-card">
                            <div class="item-card-body clearfix">
                                <div class="item comment-log-item comment-log-item-date ui-feed__timeline">
                                    <div class="ui-feed__item ui-feed__item--message">
                                        <span class="ui-feed__marker @if ($history->user_id) ui-feed__marker--user-action @endif"></span>
                                        <div class="ui-feed__message">
                                            <div class="timeline__message-container">
                                                <div class="timeline__inner-message">
                                                    <span>{!! BaseHelper::clean(OrderHelper::processHistoryVariables($history)) !!}</span>
                                                </div>
                                                <time class="timeline__time"><span>{{ $history->created_at }}</span></time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
