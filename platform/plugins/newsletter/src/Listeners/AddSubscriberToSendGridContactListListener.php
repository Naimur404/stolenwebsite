<?php

namespace Botble\Newsletter\Listeners;

use Botble\Newsletter\Events\SubscribeNewsletterEvent;
use Botble\Newsletter\Facades\Newsletter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;

class AddSubscriberToSendGridContactListListener implements ShouldQueue
{
    public function handle(SubscribeNewsletterEvent $event): void
    {
        if (! setting('enable_newsletter_contacts_list_api')) {
            return;
        }

        $sendgridApiKey = setting('newsletter_sendgrid_api_key');
        $sendgridListId = setting('newsletter_sendgrid_list_id');

        if (! $sendgridApiKey || ! $sendgridListId) {
            return;
        }

        $name = explode(' ', $event->newsletter->name);

        Newsletter::driver('sendgrid')->subscribe(
            $event->newsletter->email,
            ['first_name' => Arr::first($name), 'last_name' => Arr::get($name, '1', Arr::first($name))]
        );
    }
}
