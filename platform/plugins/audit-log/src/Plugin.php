<?php

namespace Botble\AuditLog;

use Botble\Dashboard\Models\DashboardWidget;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Widget\Models\Widget;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('audit_histories');

        Widget::query()->where('name', 'widget_audit_logs')
            ->each(fn (DashboardWidget $dashboardWidget) => $dashboardWidget->delete());
    }
}
