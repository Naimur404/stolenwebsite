<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Botble\ACL\Traits\SendsPasswordResetEmails;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Contracts\Auth\PasswordBroker;
use Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('customer.guest');
    }

    public function showLinkRequestForm()
    {
        SeoHelper::setTitle(__('Forgot Password'));

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Login'), route('customer.password.reset'));

        return Theme::scope(
            'ecommerce.customers.passwords.email',
            [],
            'plugins/ecommerce::themes.customers.passwords.email'
        )
            ->render();
    }

    public function broker(): PasswordBroker
    {
        return Password::broker('customers');
    }
}
