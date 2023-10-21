<?php

namespace Botble\Newsletter\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Newsletter\Contracts\Factory;
use Botble\Newsletter\Facades\Newsletter as NewsletterFacade;
use Botble\Newsletter\Models\Newsletter;
use Botble\Newsletter\NewsletterManager;
use Botble\Newsletter\Repositories\Eloquent\NewsletterRepository;
use Botble\Newsletter\Repositories\Interfaces\NewsletterInterface;
use Exception;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;

class NewsletterServiceProvider extends ServiceProvider implements DeferrableProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton(NewsletterInterface::class, function () {
            return new NewsletterRepository(new Newsletter());
        });

        $this->app->singleton(Factory::class, function ($app) {
            return new NewsletterManager($app);
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/newsletter')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions', 'email'])
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadMigrations();

        $this->app->register(EventServiceProvider::class);

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-newsletter',
                'priority' => 6,
                'parent_id' => null,
                'name' => 'plugins/newsletter::newsletter.name',
                'icon' => 'far fa-newspaper',
                'url' => route('newsletter.index'),
                'permissions' => ['newsletter.index'],
            ]);

            EmailHandler::addTemplateSettings(NEWSLETTER_MODULE_SCREEN_NAME, config('plugins.newsletter.email', []));
        });

        add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, function (string|null $data) {
            $mailchimpContactList = [];

            if (setting('newsletter_mailchimp_api_key')) {
                try {
                    $contacts = collect(NewsletterFacade::driver('mailchimp')->contacts());

                    if (! setting('newsletter_mailchimp_list_id')) {
                        setting()->set(['newsletter_mailchimp_list_id' => Arr::get($contacts, 'id')])->save();
                    }

                    $mailchimpContactList = $contacts->pluck('name', 'id')->toArray();
                } catch (Exception $exception) {
                    info('Caught exception: ' . $exception->getMessage());
                }
            }

            $sendGridContactList = [];

            if (setting('newsletter_sendgrid_api_key')) {
                try {
                    $contacts = collect(NewsletterFacade::driver('sendgrid')->contacts());

                    if (! setting('newsletter_sendgrid_list_id')) {
                        setting()->set(['newsletter_sendgrid_list_id' => Arr::get($contacts->first(), 'id')])->save();
                    }

                    $sendGridContactList = $contacts->pluck('name', 'id')->toArray();
                } catch (Exception $exception) {
                    info('Caught exception: ' . $exception->getMessage());
                }
            }

            return $data . view(
                'plugins/newsletter::setting',
                compact('mailchimpContactList', 'sendGridContactList')
            )->render();
        }, 249);

        add_filter('cms_settings_validation_rules', [$this, 'addSettingRules'], 249);
    }

    public function addSettingRules(array $rules): array
    {
        return array_merge($rules, [
            'enable_newsletter_contacts_list_api' => 'nullable|in:0,1',
            'newsletter_mailchimp_api_key' => 'nullable|string|min:32|max:40',
            'newsletter_mailchimp_list_id' => 'nullable|string|size:10',
            'newsletter_sendgrid_api_key' => 'nullable|string|min:32|max:255',
            'newsletter_sendgrid_list_id' => 'nullable|string|min:10|max:255',
        ]);
    }

    public function provides(): array
    {
        return [Factory::class];
    }
}
