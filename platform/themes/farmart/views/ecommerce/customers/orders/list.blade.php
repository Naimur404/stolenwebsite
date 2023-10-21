@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')
@section('content')
    <div class="section-header">
        <h3>{{ SeoHelper::getTitle() }}</h3>
    </div>
    <div class="section-content">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ __('ID number') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @if (count($orders) > 0)
                    @foreach ($orders as $order)
                        <tr>
                            <th scope="row">{{ get_order_code($order->id) }}</th>
                            <td>{{ $order->created_at->translatedFormat('M d, Y h:m') }}</td>
                            <td>{{ format_price($order->amount) }}</td>
                            <td>{!! BaseHelper::clean($order->status->toHtml()) !!}</td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="{{ route('customer.orders.view', $order->id) }}">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center">{{ __('No orders!') }}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {!! $orders->links() !!}
        </div>
    </div>
@endsection
