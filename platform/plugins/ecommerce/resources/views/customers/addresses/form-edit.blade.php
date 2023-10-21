<form action="{{ route('customers.addresses.edit.update', $address->id) }}" method="POST">
    <input type="hidden" name="customer_id" value="{{ $address->customer_id }}">

    @include('plugins/ecommerce::customers.addresses.form', ['address' => $address])
</form>
