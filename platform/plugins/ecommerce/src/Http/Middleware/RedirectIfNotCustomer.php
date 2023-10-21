<?php

namespace Botble\Ecommerce\Http\Middleware;

use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotCustomer
{
    public function handle(Request $request, Closure $next, string $guard = 'customer')
    {
        if (! Auth::guard($guard)->check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest(route('customer.login'));
        }

        $customer = Auth::guard($guard)->user();
        if ($customer->status->getValue() !== CustomerStatusEnum::ACTIVATED) {
            Auth::guard($guard)->logout();

            return redirect()
                ->guest(route('customer.login'))
                ->withErrors([
                    'email' => [
                        __('Your account has been locked, please contact the administrator.'),
                    ],
                ])
                ->withInput(['email' => $customer->email]);
        }

        return $next($request);
    }
}
