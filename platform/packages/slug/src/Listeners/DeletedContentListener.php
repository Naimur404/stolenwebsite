<?php

namespace Botble\Slug\Listeners;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;

class DeletedContentListener
{
    public function handle(DeletedContentEvent $event): void
    {
        if (SlugHelper::isSupportedModel(get_class($event->data))) {
            Slug::query()->where([
                'reference_id' => $event->data->getKey(),
                'reference_type' => get_class($event->data),
            ])->delete();
        }
    }
}
