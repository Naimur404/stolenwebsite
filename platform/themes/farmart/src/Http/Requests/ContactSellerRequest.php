<?php

namespace Theme\Farmart\Http\Requests;

use Botble\Captcha\Facades\Captcha;
use Botble\Support\Http\Requests\Request;

class ContactSellerRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'content' => 'required|string|max:1000',
        ];

        if (! auth('customer')->check()) {
            $rules += [
                'name' => 'required|string|max:40',
                'email' => 'required|email',
            ];
        }

        if (is_plugin_active('captcha')) {
            if (setting('enable_captcha_for_contact_seller')) {
                $rules += Captcha::rules();
            }

            if (setting('enable_math_captcha_for_contact_seller', 0)) {
                $rules += Captcha::mathCaptchaRules();
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return is_plugin_active('captcha') ? Captcha::attributes() : [];
    }
}
