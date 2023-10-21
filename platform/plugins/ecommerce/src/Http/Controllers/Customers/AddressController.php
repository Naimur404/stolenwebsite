<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Http\Requests\CreateAddressFromAdminRequest;
use Botble\Ecommerce\Models\Address;

class AddressController extends BaseController
{
    public function store(CreateAddressFromAdminRequest $request, BaseHttpResponse $response)
    {
        if ($request->boolean('is_default')) {
            Address::query()
                ->where([
                    'is_default' => 1,
                    'customer_id' => $request->input('customer_id'),
                ])
                ->update([
                    'is_default' => 0,
                ]);
        }

        $request->merge([
            'customer_id' => $request->input('customer_id'),
            'is_default' => $request->input('is_default', 0),
        ]);

        Address::query()->create($request->input());

        return $response
            ->setNextUrl(route('customer.address'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function update($id, CreateAddressFromAdminRequest $request, BaseHttpResponse $response)
    {
        $address = Address::query()->findOrFail($id);

        if ($request->input('is_default') == 1) {
            Address::query()
                ->where([
                    'is_default' => 1,
                    'customer_id' => $request->input('customer_id'),
                ])
                ->update([
                    'is_default' => 0,
                ]);
        }

        $request->merge([
            'customer_id' => $request->input('customer_id'),
            'is_default' => $request->input('is_default', 0),
        ]);

        $address->fill($request->input());

        $address->save();

        return $response
            ->setNextUrl(route('customer.address'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, BaseHttpResponse $response)
    {
        $address = Address::findOrFail($id);

        $address->delete();

        return $response
            ->setNextUrl(route('customer.address'))
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function edit($id)
    {
        $address = Address::findOrFail($id);

        return view('plugins/ecommerce::customers.addresses.form-edit', compact('address'))->render();
    }
}
