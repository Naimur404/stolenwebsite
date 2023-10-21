<div class="row row-cols-md-3 row-cols-sm-2 row-cols-1">
    @forelse ($addresses as $address)
        <div class="col">
            <address class="border rounded p-2">
                <p>{{ $address->name }}<br> {{ $address->address }}<br> {{ $address->city_name }}<br> {{ $address->state_name }}
                    @if (EcommerceHelper::isUsingInMultipleCountries())<br> {{ $address->country_name }} @endif
                    @if (EcommerceHelper::isZipCodeEnabled())<br> {{ $address->zip_code }} @endif
                    <br> {{ $address->phone }}
                </p>
                <div class="d-flex justify-content-between">
                    <div>
                        <a class="text-primary" href="{{ route('customer.address.edit', $address->id) }}">{{ __('Edit') }}</a>
                        <a class="text-danger btn-trigger-delete-address ms-2"
                           href="#" data-url="{{ route('customer.address.destroy', $address->id) }}">{{ __('Remove') }}</a>
                    </div>
                    @if ($address->is_default)
                        <div class="badge bg-primary">{{ __('Address Default') }}</div>
                    @endif
                </div>
            </address>
        </div>
    @empty
        <div class="col w-100">
            <div class="alert alert-warning" role="alert">
                <span class="fst-italic">{{ __('You have not set up this type of address yet.') }}</span>
            </div>
        </div>
    @endforelse
</div>

<div class="modal fade" id="confirm-delete-modal" tabindex="-1" aria-labelledby="confirm-delete-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirm-delete-modal-label">{{ __('Confirm delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Do you really want to delete this address?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary border-0 py-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary py-2 mb-0 avatar-save btn-confirm-delete">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
