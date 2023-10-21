<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Tables\VendorTable;

class VendorController extends BaseController
{
    public function index(VendorTable $table)
    {
        PageTitle::setTitle(trans('plugins/marketplace::marketplace.vendors'));

        return $table->renderTable();
    }
}
