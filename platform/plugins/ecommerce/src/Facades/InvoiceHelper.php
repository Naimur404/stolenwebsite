<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Supports\InvoiceHelper as BaseInvoiceHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed store(\Botble\Ecommerce\Models\Order $order)
 * @method static \Barryvdh\DomPDF\PDF|\Dompdf\Dompdf makeInvoicePDF(\Botble\Ecommerce\Models\Invoice $invoice)
 * @method static string generateInvoice(\Botble\Ecommerce\Models\Invoice $invoice)
 * @method static \Illuminate\Http\Response downloadInvoice(\Botble\Ecommerce\Models\Invoice $invoice)
 * @method static \Illuminate\Http\Response streamInvoice(\Botble\Ecommerce\Models\Invoice $invoice)
 * @method static string getInvoiceTemplate()
 * @method static \Botble\Ecommerce\Models\Invoice getDataForPreview()
 * @method static array getVariables()
 *
 * @see \Botble\Ecommerce\Supports\InvoiceHelper
 */
class InvoiceHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BaseInvoiceHelper::class;
    }
}
