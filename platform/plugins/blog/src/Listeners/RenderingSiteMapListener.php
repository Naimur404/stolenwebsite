<?php

namespace Botble\Blog\Listeners;

use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Blog\Models\Tag;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Botble\Theme\Facades\SiteMapManager;
use Illuminate\Support\Arr;

class RenderingSiteMapListener
{
    public function handle(RenderingSiteMapEvent $event): void
    {
        if ($key = $event->key) {
            switch ($key) {
                case 'blog-categories':
                    $categories = Category::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->select(['id', 'name', 'updated_at'])
                        ->orderByDesc('created_at')
                        ->get();

                    foreach ($categories as $category) {
                        SiteMapManager::add($category->url, $category->updated_at, '0.8');
                    }

                    break;
                case 'blog-tags':
                    $tags = Tag::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->orderByDesc('created_at')
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($tags as $tag) {
                        SiteMapManager::add($tag->url, $tag->updated_at, '0.3', 'weekly');
                    }

                    break;
            }

            if (preg_match('/^blog-posts-((?:19|20|21|22)\d{2})-(0?[1-9]|1[012])$/', $key, $matches)) {
                if (($year = Arr::get($matches, 1)) && ($month = Arr::get($matches, 2))) {
                    $posts = Post::query()
                        ->wherePublished()
                        ->whereYear('updated_at', $year)
                        ->whereMonth('updated_at', $month)
                        ->latest('updated_at')
                        ->select(['id', 'name', 'updated_at'])
                        ->with(['slugable'])
                        ->get();

                    foreach ($posts as $post) {
                        if (! $post->slugable) {
                            continue;
                        }

                        SiteMapManager::add($post->url, $post->updated_at, '0.8');
                    }
                }
            }

            return;
        }

        $posts = Post::query()
            ->selectRaw('YEAR(updated_at) as updated_year, MONTH(updated_at) as updated_month, MAX(updated_at) as updated_at')
            ->wherePublished()
            ->groupBy('updated_year', 'updated_month')
            ->orderBy('updated_year', 'desc')
            ->orderBy('updated_month', 'desc')
            ->get();

        if ($posts->isNotEmpty()) {
            foreach ($posts as $post) {
                $key = sprintf('blog-posts-%s-%s', $post->updated_year, str_pad($post->updated_month, 2, '0', STR_PAD_LEFT));
                SiteMapManager::addSitemap(SiteMapManager::route($key), $post->updated_at);
            }
        }

        $categoryLastUpdated = Category::query()
            ->wherePublished()
            ->latest('updated_at')
            ->value('updated_at');

        if ($categoryLastUpdated) {
            SiteMapManager::addSitemap(SiteMapManager::route('blog-categories'), $categoryLastUpdated);
        }

        $tagLastUpdated = Tag::query()
            ->wherePublished()
            ->latest('updated_at')
            ->value('updated_at');

        if ($tagLastUpdated) {
            SiteMapManager::addSitemap(SiteMapManager::route('blog-tags'), $tagLastUpdated);
        }
    }
}
