<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Forms\CustomerForm;
use Botble\Ecommerce\Http\Requests\AddCustomerWhenCreateOrderRequest;
use Botble\Ecommerce\Http\Requests\CustomerCreateRequest;
use Botble\Ecommerce\Http\Requests\CustomerEditRequest;
use Botble\Ecommerce\Http\Requests\CustomerUpdateEmailRequest;
use Botble\Ecommerce\Http\Resources\CustomerAddressResource;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Tables\CustomerReviewTable;
use Botble\Ecommerce\Tables\CustomerTable;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerController extends BaseController
{
    public function index(CustomerTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::customer.name'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/ecommerce::customer.create'));

        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/customer.js');

        return $formBuilder->create(CustomerForm::class)->remove('is_change_password')->renderForm();
    }

    public function store(CustomerCreateRequest $request, BaseHttpResponse $response)
    {
        $customer = new Customer();
        $customer->fill($request->input());
        $customer->confirmed_at = Carbon::now();
        $customer->password = Hash::make($request->input('password'));
        $customer->dob = Carbon::parse($request->input('dob'))->toDateString();
        $customer->save();

        event(new CreatedContentEvent(CUSTOMER_MODULE_SCREEN_NAME, $request, $customer));

        return $response
            ->setPreviousUrl(route('customers.index'))
            ->setNextUrl(route('customers.edit', $customer->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder)
    {
        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/customer.js');

        $customer = Customer::query()->findOrFail($id);

        PageTitle::setTitle(trans('plugins/ecommerce::customer.edit', ['name' => $customer->name]));

        $customer->password = null;

        return $formBuilder->create(CustomerForm::class, ['model' => $customer])->renderForm();
    }

    public function update(int|string $id, CustomerEditRequest $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()->findOrFail($id);

        $customer->fill($request->except('password'));

        if ($request->input('is_change_password') == 1) {
            $customer->password = Hash::make($request->input('password'));
        }

        $customer->dob = Carbon::parse($request->input('dob'))->toDateString();

        $customer->save();

        event(new UpdatedContentEvent(CUSTOMER_MODULE_SCREEN_NAME, $request, $customer));

        return $response
            ->setPreviousUrl(route('customers.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $customer = Customer::query()->findOrFail($id);
            $customer->delete();
            event(new DeletedContentEvent(CUSTOMER_MODULE_SCREEN_NAME, $request, $customer));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function verifyEmail(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()
            ->where([
                'id' => $id,
                'confirmed_at' => null,
            ])->firstOrFail();

        $customer->confirmed_at = Carbon::now();
        $customer->save();

        event(new UpdatedContentEvent(CUSTOMER_MODULE_SCREEN_NAME, $request, $customer));

        return $response
            ->setPreviousUrl(route('customers.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function getListCustomerForSelect(BaseHttpResponse $response)
    {
        $customers = Customer::query()
            ->select(['id', 'name'])
            ->get()
            ->toArray();

        return $response->setData($customers);
    }

    public function getListCustomerForSearch(Request $request, BaseHttpResponse $response)
    {
        $customers = Customer::query()
            ->where('name', 'LIKE', '%' . $request->input('keyword') . '%')
            ->simplePaginate(5);

        foreach ($customers as &$customer) {
            $customer->avatar_url = (string)$customer->avatar_url;
        }

        return $response->setData($customers);
    }

    public function postUpdateEmail($id, CustomerUpdateEmailRequest $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()->findOrFail($id);

        $customer->email = $request->input('email');
        $customer->save();

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function getCustomerAddresses($id, BaseHttpResponse $response)
    {
        $addresses = Address::query()->where('customer_id', $id)->get();

        return $response->setData(CustomerAddressResource::collection($addresses));
    }

    public function getCustomerOrderNumbers($id, BaseHttpResponse $response)
    {
        $customer = Customer::query()->find($id);
        if (! $customer) {
            return $response->setData(0);
        }

        return $response->setData($customer->orders()->count());
    }

    public function postCreateCustomerWhenCreatingOrder(
        AddCustomerWhenCreateOrderRequest $request,
        BaseHttpResponse $response
    ) {
        $request->merge(['password' => Hash::make(Str::random(36))]);
        $customer = Customer::query()->create($request->input());
        $customer->avatar = (string)$customer->avatar_url;

        event(new CreatedContentEvent(CUSTOMER_MODULE_SCREEN_NAME, $request, $customer));

        $request->merge([
            'customer_id' => $customer->id,
            'is_default' => true,
        ]);

        $address = Address::query()->create($request->input());

        $address->country = $address->country_name;
        $address->state = $address->state_name;
        $address->city = $address->city_name;

        $address->country_name = $address->country;
        $address->state_name = $address->state;
        $address->city_name = $address->city;

        return $response
            ->setData(compact('address', 'customer'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function ajaxReviews(int|string $id, CustomerReviewTable $customerReviewTable)
    {
        return $customerReviewTable->customerId($id)->renderTable();
    }
}
