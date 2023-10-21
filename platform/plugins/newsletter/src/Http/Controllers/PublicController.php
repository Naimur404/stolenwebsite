<?php

namespace Botble\Newsletter\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Newsletter\Enums\NewsletterStatusEnum;
use Botble\Newsletter\Events\SubscribeNewsletterEvent;
use Botble\Newsletter\Events\UnsubscribeNewsletterEvent;
use Botble\Newsletter\Http\Requests\NewsletterRequest;
use Botble\Newsletter\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;

class PublicController extends Controller
{
    public function postSubscribe(NewsletterRequest $request, BaseHttpResponse $response)
    {
        $newsletter = Newsletter::query()->where('email', $request->input('email'))->first();

        if (! $newsletter) {
            $newsletter = Newsletter::query()->create($request->input());
        } else {
            $newsletter->status = NewsletterStatusEnum::SUBSCRIBED;
            $newsletter->save();
        }

        event(new SubscribeNewsletterEvent($newsletter));

        return $response->setMessage(__('Subscribe to newsletter successfully!'));
    }

    public function getUnsubscribe(int|string $id, Request $request, BaseHttpResponse $response)
    {
        if (! URL::hasValidSignature($request)) {
            abort(404);
        }

        $newsletter = Newsletter::query()
            ->where([
                'id' => $id,
                'status' => NewsletterStatusEnum::SUBSCRIBED,
            ])
            ->first();

        if ($newsletter) {
            $newsletter->status = NewsletterStatusEnum::UNSUBSCRIBED;
            $newsletter->save();

            event(new UnsubscribeNewsletterEvent($newsletter));

            return $response
                ->setNextUrl(route('public.index'))
                ->setMessage(__('Unsubscribe to newsletter successfully'));
        }

        return $response
            ->setError()
            ->setNextUrl(route('public.index'))
            ->setMessage(__('Your email does not exist in the system or you have unsubscribed already!'));
    }
}
