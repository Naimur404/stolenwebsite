<?php

namespace Botble\Newsletter\Listeners;

use Botble\Newsletter\Events\SubscribeNewsletterEvent;
use Botble\Newsletter\Facades\Newsletter;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddSubscriberToMailchimpContactListListener implements ShouldQueue
{
    public function handle(SubscribeNewsletterEvent $event): void
    {
        if (! setting('enable_newsletter_contacts_list_api')) {
            return;
        }

        $mailchimpApiKey = setting('newsletter_mailchimp_api_key');
        $mailchimpListId = setting('newsletter_mailchimp_list_id');

        if (! $mailchimpApiKey || ! $mailchimpListId) {
            return;
        }

        Newsletter::driver('mailchimp')->subscribe($event->newsletter->email);
    }
}
