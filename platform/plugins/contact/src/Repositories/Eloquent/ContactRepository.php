<?php

namespace Botble\Contact\Repositories\Eloquent;

use Botble\Contact\Enums\ContactStatusEnum;
use Botble\Contact\Repositories\Interfaces\ContactInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class ContactRepository extends RepositoriesAbstract implements ContactInterface
{
    public function getUnread(array $select = ['*']): Collection
    {
        $data = $this->model
            ->where('status', ContactStatusEnum::UNREAD)
            ->select($select)
            ->orderByDesc('created_at')
            ->get();

        $this->resetModel();

        return $data;
    }

    public function countUnread(): int
    {
        $data = $this->model->where('status', ContactStatusEnum::UNREAD)->count();
        $this->resetModel();

        return $data;
    }
}
