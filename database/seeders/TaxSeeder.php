<?php

namespace Database\Seeders;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Tax;

class TaxSeeder extends BaseSeeder
{
    public function run(): void
    {
        Tax::query()->truncate();

        Tax::query()->create([
            'title' => 'VAT',
            'percentage' => 10,
            'priority' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Tax::query()->create([
            'title' => 'None',
            'percentage' => 0,
            'priority' => 2,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Tax::query()->create([
            'title' => 'Import Tax',
            'percentage' => 15,
            'priority' => 3,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }
}
