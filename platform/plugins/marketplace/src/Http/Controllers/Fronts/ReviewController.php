<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Tables\ReviewTable;

class ReviewController
{
    public function index(ReviewTable $table)
    {
        PageTitle::setTitle(__('Reviews'));

        Assets::addStylesDirectly('vendor/core/plugins/ecommerce/css/review.css');

        return $table->render(MarketplaceHelper::viewPath('dashboard.table.base'));
    }
}
