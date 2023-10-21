<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Botble\ACL\Traits\RegistersUsers;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\RegisterRequest;
use Botble\Ecommerce\Models\Customer;
use Botble\JsValidation\Facades\JsValidator;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('customer.guest');
    }

    public function showRegistrationForm()
    {
        SeoHelper::setTitle(__('Register'));

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Register'), route('customer.register'));

        if (! session()->has('url.intended') &&
            ! in_array(url()->previous(), [route('customer.login'), route('customer.register')])
        ) {
            session(['url.intended' => url()->previous()]);
        }

        Theme::asset()
            ->container('footer')
            ->usePath(false)
            ->add('js-validation', 'vendor/core/core/js-validation/js/js-validation.js', ['jquery']);

        add_filter(THEME_FRONT_FOOTER, function ($html) {
            return $html . JsValidator::formRequest(RegisterRequest::class)->render();
        });

        return Theme::scope('ecommerce.customers.register', [], 'plugins/ecommerce::themes.customers.register')
            ->render();
    }

    public function register(Request $request, BaseHttpResponse $response)
    {
        $this->validator($request->input())->validate();

        do_action('customer_register_validation', $request);

        $customer = $this->create($request->input());

        event(new Registered($customer));

        if (EcommerceHelper::isEnableEmailVerification()) {
            $this->registered($request, $customer);

            return $response
                    ->setNextUrl(route('customer.login'))
                    ->setMessage(__('We have sent you an email to verify your email. Please check and confirm your email address!'));
        }

        $customer->confirmed_at = Carbon::now();
        $customer->save();

        $this->guard()->login($customer);

        return $response->setNextUrl($this->redirectPath())->setMessage(__('Registered successfully!'));
    }

    protected function validator(array $data)
    {
        return Validator::make($data, (new RegisterRequest())->rules());
    }

    protected function create(array $data)
    {
        return Customer::query()->create([
            'name' => BaseHelper::clean($data['name']),
            'email' => BaseHelper::clean($data['email']),
            'password' => Hash::make($data['password']),
        ]);
    }

    protected function guard()
    {
        return auth('customer');
    }

    public function confirm(int|string $id, Request $request, BaseHttpResponse $response)
    {
        if (! URL::hasValidSignature($request)) {
            abort(404);
        }

        $customer = Customer::query()->findOrFail($id);

        $customer->confirmed_at = Carbon::now();
        $customer->save();

        $this->guard()->login($customer);

        return $response
            ->setNextUrl(route('customer.overview'))
            ->setMessage(__('You successfully confirmed your email address.'));
    }

    public function resendConfirmation(
        Request $request,
        BaseHttpResponse $response
    ) {
        $customer =Customer::query()->where('email', $request->input('email'))->first();

        if (! $customer) {
            return $response
                ->setError()
                ->setMessage(__('Cannot find this customer!'));
        }

        $customer->sendEmailVerificationNotification();

        return $response
            ->setMessage(__('We sent you another confirmation email. You should receive it shortly.'));
    }
}
