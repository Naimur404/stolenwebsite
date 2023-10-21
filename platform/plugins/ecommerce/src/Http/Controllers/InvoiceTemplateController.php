<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Supports\InvoiceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class InvoiceTemplateController extends BaseController
{
    public function index(InvoiceHelper $invoiceHelper)
    {
        Assets::addStylesDirectly([
            'vendor/core/core/base/libraries/codemirror/lib/codemirror.css',
            'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.css',
            'vendor/core/core/setting/css/setting.css',
        ])
            ->addScriptsDirectly([
                'vendor/core/core/base/libraries/codemirror/lib/codemirror.js',
                'vendor/core/core/base/libraries/codemirror/lib/css.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/anyword-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/css-hint.js',
                'vendor/core/core/setting/js/setting.js',
            ]);

        $content = $invoiceHelper->getInvoiceTemplate();
        $variables = $invoiceHelper->getVariables();

        return view('plugins/ecommerce::invoice-template.edit', compact('content', 'variables'));
    }

    public function update(Request $request, BaseHttpResponse $response)
    {
        BaseHelper::saveFileData(storage_path('app/templates/invoice.tpl'), $request->input('content'), false);

        return $response->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function reset(BaseHttpResponse $response)
    {
        File::delete(storage_path('app/templates/invoice.tpl'));

        return $response->setMessage(trans('core/setting::setting.email.reset_success'));
    }

    public function preview(InvoiceHelper $invoiceHelper)
    {
        $invoice = $invoiceHelper->getDataForPreview();

        return $invoiceHelper->streamInvoice($invoice);
    }
}
