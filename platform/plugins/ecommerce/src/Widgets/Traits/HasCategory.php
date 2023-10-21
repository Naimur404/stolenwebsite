<?php

namespace Botble\Ecommerce\Widgets\Traits;

use Carbon\Carbon;

trait HasCategory
{
    protected function translateCategories(array $data): array
    {
        $categories = [];

        foreach (array_keys($data) as $key => $item) {
            $replacement = [
                '%h %d' => '%h',
                '%d %b' => '%d M',
                '%b %Y' => '%M Y',
                '%' => '',
            ];

            $displayFormat = $this->dateFormat;

            foreach ($replacement as $replacementKey => $value) {
                $displayFormat = str_replace($replacementKey, $value, $displayFormat);
            }

            $dataFormat = str_replace('%', '', str_replace('%b', '%M', $this->dateFormat));

            $categories[$key] = Carbon::createFromFormat($dataFormat, $item)->translatedFormat($displayFormat);
        }

        return $categories;
    }
}
