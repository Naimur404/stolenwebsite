<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ShippingRuleItemImportRequest extends Request
{
    public function rules(): array
    {
        $mimeType = implode(',', config('plugins.ecommerce.general.bulk-import.mime_types'));

        return [
            'file' => 'required|file|mimetypes:' . $mimeType,
            'type' => 'required|in:overwrite,add_new,skip',
        ];
    }
}
