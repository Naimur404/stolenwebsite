<div class="widget-body p-0" id="address-histories">
    <a href="#" class="btn-trigger-add-address btn btn-sm btn-info mb-3"><i class="fa fa-plus"></i> {{ trans('plugins/ecommerce::addresses.new_address') }}</a>
    <div class="comment-log-timeline">
        <div class="column-left-history ps-relative" id="order-history-wrapper">
            <div class="item-card">
                    <div class="item-card-body ">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">{{ trans('plugins/ecommerce::addresses.address') }}</th>
                                <th scope="col">{{ trans('plugins/ecommerce::addresses.zip') }}</th>
                                <th scope="col">{{ trans('plugins/ecommerce::addresses.country') }}</th>
                                <th scope="col">{{ trans('plugins/ecommerce::addresses.state') }}</th>
                                <th scope="col">{{ trans('plugins/ecommerce::addresses.city') }}</th>
                                <th scope="col">{{ trans('plugins/ecommerce::addresses.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($addresses as $address)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-start"> {{ $address->address }}</td>
                                    <td>{{ $address->zip_code }}</td>
                                    <td>{{ $address->country_name }}</td>
                                    <td>{{ $address->state_name }}</td>
                                    <td>{{ $address->city_name }}</td>
                                    <td class="text-center" style="width: 120px;">
                                        <a href="#" class="btn btn-icon btn-sm btn-info me-1 btn-trigger-edit-address"
                                           data-bs-toggle="tooltip" data-section="{{ route('customers.addresses.edit', $address->id) }}"
                                           role="button" data-bs-original-title="">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-icon btn-sm btn-danger deleteDialog"
                                           data-bs-toggle="tooltip" data-section="{{ route('customers.addresses.destroy', $address->id) }}"
                                           role="button" data-bs-original-title="{{ trans('core/base::forms.edit') }}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <td colspan="7" class="text-center">{{ trans('plugins/ecommerce::addresses.no_data') }}</td>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>
</div>
