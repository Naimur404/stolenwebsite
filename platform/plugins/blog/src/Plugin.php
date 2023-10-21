<?php

namespace Botble\Blog;

use Botble\Blog\Models\Category;
use Botble\Blog\Models\Tag;
use Botble\Dashboard\Models\DashboardWidget;
use Botble\Menu\Models\MenuNode;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Botble\Widget\Models\Widget;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('post_tags');
        Schema::dropIfExists('post_categories');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('posts_translations');
        Schema::dropIfExists('categories_translations');
        Schema::dropIfExists('tags_translations');

        Widget::query()->where('name', 'widget_posts_recent')
            ->each(fn (DashboardWidget $dashboardWidget) => $dashboardWidget->delete());

        MenuNode::query()->whereIn('reference_type', [Category::class, Tag::class])
            ->each(fn (MenuNode $menuNode) => $menuNode->delete());

        Setting::delete([
            'blog_post_schema_enabled',
            'blog_post_schema_type',
        ]);
    }
}
