<?php

namespace Botble\Ecommerce\Charts;

use Botble\Chart\LineChart;
use Botble\Chart\Supports\Base;

class TimeChart extends LineChart
{
    public function init(): Base
    {
        return $this
            ->setElementId('ecommerce-time-chart')
            ->xkey(['date'])
            ->ykeys(['revenue'])
            ->pointFillColors(['green'])
            ->pointStrokeColors(['black'])
            ->lineColors(['blue', 'pink'])
            ->hoverCallback('function(index, options, content, row) {return "<strong>" + row.formatted_date + "</strong>: " + row.formatted_revenue;}')
            ->xLabels('day');
    }
}
