<?php

namespace Botble\Newsletter\Http\Requests;

use Botble\Captcha\Facades\Captcha;
use Botble\Newsletter\Enums\NewsletterStatusEnum;
use Botble\Newsletter\Models\Newsletter;
use Botble\Support\Http\Requests\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class NewsletterRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'email' => [
                'required',
                'email',
                Rule::unique(Newsletter::getModel()->getTable())->where(function (Builder $query) {
                    $query->where('status', NewsletterStatusEnum::SUBSCRIBED);
                }),
            ],
            'status' => Rule::in(NewsletterStatusEnum::values()),
        ];

        if (is_plugin_active('captcha')) {
            $rules += Captcha::rules();

            if (setting('enable_math_captcha_for_newsletter_form', 0)) {
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
