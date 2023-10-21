<x-core-setting::section
    :title="trans('plugins/newsletter::newsletter.settings.title')"
    :description="trans('plugins/newsletter::newsletter.settings.description')"
>
    <x-core-setting::on-off
        name="enable_newsletter_contacts_list_api"
        :label="trans('plugins/newsletter::newsletter.settings.enable_newsletter_contacts_list_api')"
        :value="setting('enable_newsletter_contacts_list_api', false)"
        class="setting-selection-option"
        data-target="#newsletter-settings"
    />

    <div id="newsletter-settings" @class(['mb-4 border rounded-top rounded-bottom p-3 bg-light', 'd-none' => ! setting('enable_newsletter_contacts_list_api', false)])>
        <x-core-setting::text-input
            name="newsletter_mailchimp_api_key"
            :label="trans('plugins/newsletter::newsletter.settings.mailchimp_api_key')"
            :value="setting('newsletter_mailchimp_api_key')"
            :placeholder="trans('plugins/newsletter::newsletter.settings.mailchimp_api_key')"
            data-counter="120"
        />

        @if (empty($mailchimpContactList))
            <x-core-setting::text-input
                name="newsletter_mailchimp_list_id"
                :label="trans('plugins/newsletter::newsletter.settings.mailchimp_list_id')"
                :value="setting('newsletter_mailchimp_list_id')"
                :placeholder="trans('plugins/newsletter::newsletter.settings.mailchimp_list_id')"
                data-counter="120"
            />
        @else
            <x-core-setting::select
                name="newsletter_mailchimp_list_id"
                :label="trans('plugins/newsletter::newsletter.settings.mailchimp_list')"
                :options="$mailchimpContactList"
                :value="setting('newsletter_mailchimp_list_id')"
            />
        @endif

        <x-core-setting::text-input
            name="newsletter_sendgrid_api_key"
            :label="trans('plugins/newsletter::newsletter.settings.sendgrid_api_key')"
            :value="setting('newsletter_sendgrid_api_key')"
            :placeholder="trans('plugins/newsletter::newsletter.settings.sendgrid_api_key')"
            data-counter="120"
        />

        @if (empty($sendGridContactList))
            <x-core-setting::text-input
                name="newsletter_sendgrid_list_id"
                :label="trans('plugins/newsletter::newsletter.settings.sendgrid_list_id')"
                :value="setting('newsletter_sendgrid_list_id')"
                :placeholder="trans('plugins/newsletter::newsletter.settings.sendgrid_list_id')"
                data-counter="120"
            />
        @else
            <x-core-setting::select
                name="newsletter_sendgrid_list_id"
                :label="trans('plugins/newsletter::newsletter.settings.sendgrid_list')"
                :options="$sendGridContactList"
                :value="setting('newsletter_sendgrid_list_id')"
            />
        @endif
    </div>
</x-core-setting::section>
