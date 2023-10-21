<?php

namespace Botble\Marketplace\Providers;

use Botble\ACL\Models\User;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\Form;
use Botble\Base\Facades\MacroableModels;
use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Middleware\RedirectIfNotVendor;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\VendorInfo;
use Botble\Marketplace\Models\Withdrawal;
use Botble\Marketplace\Repositories\Eloquent\RevenueRepository;
use Botble\Marketplace\Repositories\Eloquent\StoreRepository;
use Botble\Marketplace\Repositories\Eloquent\VendorInfoRepository;
use Botble\Marketplace\Repositories\Eloquent\WithdrawalRepository;
use Botble\Marketplace\Repositories\Interfaces\RevenueInterface;
use Botble\Marketplace\Repositories\Interfaces\StoreInterface;
use Botble\Marketplace\Repositories\Interfaces\VendorInfoInterface;
use Botble\Marketplace\Repositories\Interfaces\WithdrawalInterface;
use Botble\Optimize\Facades\OptimizerHelper;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\SiteMapManager;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MarketplaceServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this->app->bind(StoreInterface::class, function () {
            return new StoreRepository(new Store());
        });

        $this->app->bind(RevenueInterface::class, function () {
            return new RevenueRepository(new Revenue());
        });

        $this->app->bind(WithdrawalInterface::class, function () {
            return new WithdrawalRepository(new Withdrawal());
        });

        $this->app->bind(VendorInfoInterface::class, function () {
            return new VendorInfoRepository(new VendorInfo());
        });

        Helper::autoload(__DIR__ . '/../../helpers');

        $this->app['router']->aliasMiddleware('vendor', RedirectIfNotVendor::class);

        $loader = AliasLoader::getInstance();
        $loader->alias('MarketplaceHelper', MarketplaceHelper::class);
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }
        $this->setNamespace('plugins/marketplace')
            ->loadAndPublishConfigurations(['permissions', 'assets', 'email', 'general'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadRoutes(['base', 'fronts']);

        if (defined('LANGUAGE_MODULE_SCREEN_NAME') && defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            LanguageAdvancedManager::registerModule(Store::class, [
                'name',
                'description',
                'content',
                'address',
                'company',
            ]);
        }

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-marketplace-settings',
                    'priority' => 999,
                    'parent_id' => 'cms-plugins-marketplace',
                    'name' => 'plugins/marketplace::marketplace.settings.name',
                    'icon' => null,
                    'url' => route('marketplace.settings'),
                    'permissions' => ['marketplace.settings'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-marketplace',
                    'priority' => 9,
                    'parent_id' => null,
                    'name' => 'plugins/marketplace::marketplace.name',
                    'icon' => 'fas fa-project-diagram',
                    'url' => '#',
                    'permissions' => ['marketplace.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-store',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-marketplace',
                    'name' => 'plugins/marketplace::store.name',
                    'icon' => null,
                    'url' => route('marketplace.store.index'),
                    'permissions' => ['marketplace.store.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-withdrawal',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-marketplace',
                    'name' => 'plugins/marketplace::withdrawal.name',
                    'icon' => null,
                    'url' => route('marketplace.withdrawal.index'),
                    'permissions' => ['marketplace.withdrawal.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-marketplace-vendors',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-marketplace',
                    'name' => 'plugins/marketplace::marketplace.vendors',
                    'icon' => null,
                    'url' => route('marketplace.vendors.index'),
                    'permissions' => ['marketplace.vendors.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-marketplace-reports',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-marketplace',
                    'name' => 'plugins/marketplace::marketplace.reports.name',
                    'icon' => null,
                    'url' => route('marketplace.reports.index'),
                    'permissions' => ['marketplace.reports'],
                ]);

            if (MarketplaceHelper::getSetting('verify_vendor', 1)) {
                DashboardMenu::registerItem([
                    'id' => 'cms-plugins-marketplace-unverified-vendor',
                    'priority' => 5,
                    'parent_id' => 'cms-plugins-marketplace',
                    'name' => 'plugins/marketplace::unverified-vendor.name',
                    'icon' => null,
                    'url' => route('marketplace.unverified-vendors.index'),
                    'permissions' => ['marketplace.unverified-vendors.index'],
                ]);
            } else {
                config([
                    'plugins.marketplace.email.templates' => Arr::except(
                        config('plugins.marketplace.email.templates'),
                        'verify_vendor'
                    ),
                ]);
            }

            EmailHandler::addTemplateSettings(
                MARKETPLACE_MODULE_SCREEN_NAME,
                config('plugins.marketplace.email', [])
            );

            add_filter(IS_IN_ADMIN_FILTER, [$this, 'setInAdmin'], 128);
        });

        SlugHelper::registerModule(Store::class, 'Stores');
        SlugHelper::setPrefix(Store::class, 'stores');

        SeoHelper::registerModule([Store::class]);
        SiteMapManager::registerKey('stores');

        $this->app->register(EventServiceProvider::class);
        $this->app->register(HookServiceProvider::class);
        $this->app->register(OrderSupportServiceProvider::class);

        $this->app['events']->listen('eloquent.deleted: ' . Customer::class, function (Customer $customer) {
            Revenue::query()->where('customer_id', $customer->id)->delete();
            Withdrawal::query()->where('customer_id', $customer->id)->delete();
            VendorInfo::query()->where('customer_id', $customer->id)->delete();
        });

        $this->app->booted(function () {
            Customer::resolveRelationUsing('store', function ($model) {
                return $model->hasOne(Store::class)->withDefault();
            });

            Order::resolveRelationUsing('store', function ($model) {
                return $model->belongsTo(Store::class, 'store_id')->withDefault();
            });

            Product::resolveRelationUsing('store', function ($model) {
                return $model->belongsTo(Store::class, 'store_id')->withDefault();
            });

            Product::resolveRelationUsing('approvedBy', function ($model) {
                return $model->belongsTo(User::class, 'approved_by')->withDefault();
            });

            Customer::resolveRelationUsing('vendorInfo', function ($model) {
                return $model->hasOne(VendorInfo::class, 'customer_id')->withDefault();
            });

            Discount::resolveRelationUsing('store', function ($model) {
                return $model->belongsTo(Store::class, 'store_id')->withDefault();
            });

            MacroableModels::addMacro(Customer::class, 'getBalanceAttribute', function () {
                /**
                 * @return float
                 * @var BaseModel $this
                 */
                return $this->vendorInfo ? $this->vendorInfo->balance : 0;
            });

            MacroableModels::addMacro(Customer::class, 'getBankInfoAttribute', function () {
                /**
                 * @return array
                 * @var BaseModel $this
                 */
                return $this->vendorInfo ? $this->vendorInfo->bank_info : [];
            });

            MacroableModels::addMacro(Customer::class, 'getTaxInfoAttribute', function () {
                /**
                 * @return array
                 * @var BaseModel $this
                 */
                return $this->vendorInfo ? $this->vendorInfo->tax_info : [];
            });

            MacroableModels::addMacro(Customer::class, 'getTotalFeeAttribute', function () {
                /**
                 * @return float
                 * @var BaseModel $this
                 */
                return $this->vendorInfo ? $this->vendorInfo->total_fee : 0;
            });

            MacroableModels::addMacro(Customer::class, 'getTotalRevenueAttribute', function () {
                /**
                 * @return float
                 * @var BaseModel $this
                 */
                return $this->vendorInfo ? $this->vendorInfo->total_revenue : 0;
            });

            if (is_plugin_active('language-advanced')) {
                $this->loadRoutes(['language-advanced']);
            }
        });

        Form::component('customEditor', MarketplaceHelper::viewPath('dashboard.forms.partials.custom-editor'), [
            'name',
            'value' => null,
            'attributes' => [],
        ]);

        Form::component('customImage', MarketplaceHelper::viewPath('dashboard.forms.partials.custom-image'), [
            'name',
            'value' => null,
            'attributes' => [],
        ]);

        Form::component('customImages', MarketplaceHelper::viewPath('dashboard.forms.partials.custom-images'), [
            'name',
            'values' => null,
            'attributes' => [],
        ]);
    }

    public function setInAdmin(bool $isInAdmin): bool
    {
        $isInAdmin = in_array('vendor', Route::current()->middleware()) || $isInAdmin;

        if ($isInAdmin) {
            OptimizerHelper::disable();
        }

        return $isInAdmin;
    }
}
