<?php

namespace Botble\Newsletter;

use Botble\Newsletter\Contracts\Factory;
use Botble\Newsletter\Drivers\MailChimp;
use Botble\Newsletter\Drivers\SendGrid;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class NewsletterManager extends Manager implements Factory
{
    protected function createMailChimpDriver(): MailChimp
    {
        return new MailChimp(
            setting('newsletter_mailchimp_api_key'),
            setting('newsletter_mailchimp_list_id')
        );
    }

    protected function createSendGridDriver(): SendGrid
    {
        return new SendGrid(
            setting('newsletter_sendgrid_api_key'),
            setting('newsletter_sendgrid_list_id')
        );
    }

    public function getDefaultDriver(): string
    {
        throw new InvalidArgumentException('No email marketing provider was specified.');
    }
}
