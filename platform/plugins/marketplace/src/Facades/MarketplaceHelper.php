<?php

namespace Botble\Marketplace\Facades;

use Botble\Marketplace\Supports\MarketplaceHelper as MarketplaceHelperSupport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed view(string $view, array $data = [])
 * @method static string viewPath(string $view)
 * @method static array|string|int|bool|null getSetting(string $key, array|string|int|bool|null $default = '')
 * @method static string getSettingKey(string $key = '')
 * @method static array discountTypes()
 * @method static string getAssetVersion()
 * @method static bool hideStorePhoneNumber()
 * @method static bool hideStoreEmail()
 * @method static bool hideStoreSocialLinks()
 * @method static bool allowVendorManageShipping()
 * @method static mixed sendMailToVendorAfterProcessingOrder($orders)
 * @method static \Botble\Base\Supports\EmailHandler setEmailVendorVariables(\Botble\Ecommerce\Models\Order $order)
 * @method static bool isCommissionCategoryFeeBasedEnabled()
 * @method static int maxFilesizeUploadByVendor()
 * @method static int maxProductImagesUploadByVendor()
 *
 * @see \Botble\Marketplace\Supports\MarketplaceHelper
 */
class MarketplaceHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MarketplaceHelperSupport::class;
    }
}
