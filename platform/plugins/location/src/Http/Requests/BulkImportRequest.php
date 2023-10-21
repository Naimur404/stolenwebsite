<?php

namespace Botble\Location\Http\Requests;

use Botble\Support\Http\Requests\Request;

class BulkImportRequest extends Request
{
    public function rules(): array
    {
        $mimeType = implode(',', config('plugins.location.general.bulk-import.mime_types', []));

        return [
            'file' => 'required|file|mimetypes:' . $mimeType,
            'type' => 'required|in:all,countries,states,cities',
        ];
    }
}
