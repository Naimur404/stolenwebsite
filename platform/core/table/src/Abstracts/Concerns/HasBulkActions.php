<?php

namespace Botble\Table\Abstracts\Concerns;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Models\BaseModel;
use Botble\Table\Abstracts\TableBulkActionAbstract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

trait HasBulkActions
{
    /**
     * @var \Botble\Table\Abstracts\TableBulkActionAbstract[]|class-string<\Botble\Table\Abstracts\TableBulkActionAbstract>[]
     */
    protected array $bulkActions = [];

    /**
     * @var \Botble\Table\Abstracts\TableBulkActionAbstract[]
     */
    protected array $bulkActionsCaches;

    /**
     * @var array[] $bulkActions
     */
    protected array $bulkChanges = [];

    protected string $bulkChangeUrl = '';

    public function bulkActions(): array
    {
        return [];
    }

    /**
     * @param \Botble\Table\Abstracts\TableBulkActionAbstract[] $bulkActions
     */
    public function addBulkActions(array $bulkActions): static
    {
        $this->bulkActions = $bulkActions;

        return $this;
    }

    public function hasBulkActions(): bool
    {
        return ! empty($this->getBulkActions());
    }

    public function getBulkActions(): array
    {
        return $this->bulkActionsCaches ??= collect()
            ->when(
                $bulkChanges = $this->getAllBulkChanges(),
                function (Collection $collection) use ($bulkChanges) {
                    return $collection->merge([
                        -1 => view('core/table::bulk-changes', [
                            'bulk_changes' => $bulkChanges,
                            'class' => get_class($this),
                            'url' => $this->bulkChangeUrl ?: route('tables.bulk-change.save'),
                        ])->render(),
                    ]);
                }
            )
            ->merge(array_merge($this->bulkActions(), $this->bulkActions))
            ->mapWithKeys(function ($action, $key) {
                if (is_string($action) && class_exists($action) || $action instanceof TableBulkActionAbstract) {
                    $action = $action instanceof TableBulkActionAbstract ? $action : app($action);
                    $action->table($this);
                    $key = get_class($action);
                }

                return [$key => $action];
            })
            ->reject(function ($action) {
                if ($action instanceof TableBulkActionAbstract) {
                    return ! $action->currentUserHasAnyPermissions();
                }

                return false;
            })
            ->sortBy(function ($action, $key) {
                if ($action instanceof TableBulkActionAbstract) {
                    return $action->getPriority();
                }

                return $key;
            })
            ->toArray();
    }

    protected function determineIfBulkActionsRequest(): bool
    {
        $request = $this->request();

        try {
            return $request->ajax()
                && $request->validate([
                    'bulk_action' => ['sometimes', 'required', 'boolean'],
                    'bulk_action_target' => ['required_with:bulk_action' , 'string'],
                    'ids' => ['required_with:bulk_action' , 'array'],
                    'ids.*' => ['required'],
                ])
                && class_exists($request->input('bulk_action_target'));
        } catch (ValidationException) {
            return false;
        }
    }

    protected function findBulkAction(string $bulkAction): TableBulkActionAbstract|false
    {
        if (class_exists($bulkAction) && key_exists($bulkAction, $this->getBulkActions())) {
            return $this->bulkActionsCaches[$bulkAction];
        }

        return false;
    }

    public function dispatchBulkAction(): BaseHttpResponse
    {
        $bulkAction = $this->findBulkAction(
            $this->request()->input('bulk_action_target')
        );

        $ids = Arr::wrap($this->request()->input('ids'));

        if (! $bulkAction) {
            return (new BaseHttpResponse())
                ->setError()
                ->setMessage(trans('core/table::invalid_bulk_action'));
        }

        if (empty($ids)) {
            return (new BaseHttpResponse())
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        try {
            $model = $this->getModel();

            $bulkAction->handleBeforeDispatch($model, $ids);

            $response = $bulkAction->dispatch($model, $ids);

            return tap($response, fn () => $bulkAction->handleAfterDispatch($model, $ids));
        } catch (Throwable $exception) {
            return (new BaseHttpResponse())
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    /**
     * @param array[] $bulkChanges
     */
    public function addBulkChanges(array $bulkChanges): static
    {
        $this->bulkChanges = $bulkChanges;

        return $this;
    }

    public function getAllBulkChanges(): array
    {
        return array_merge($this->getBulkChanges(), $this->bulkChanges);
    }

    public function saveBulkChanges(array $ids, string $inputKey, string|null $inputValue): bool
    {
        if (! in_array($inputKey, array_keys($this->getAllBulkChanges()))) {
            return false;
        }

        $request = request();

        foreach ($ids as $id) {
            $item = $this->getModel()->query()->findOrFail($id);

            /**
             * @var BaseModel $item
             */
            $item = $this->saveBulkChangeItem($item, $inputKey, $inputValue);

            event(new UpdatedContentEvent($this->getModel(), $request, $item));
        }

        return true;
    }

    public function saveBulkChangeItem(Model $item, string $inputKey, string|null $inputValue)
    {
        $item->{Auth::check() ? 'forceFill' : 'fill'}([$inputKey => $this->prepareBulkChangeValue($inputKey, $inputValue)]);

        $item->save();

        return $item;
    }

    public function prepareBulkChangeValue(string $key, string|null $value): string
    {
        if (strpos($key, '.') !== -1) {
            $key = Arr::last(explode('.', $key));
        }

        switch ($key) {
            case 'created_at':
            case 'updated_at':
                $value = BaseHelper::formatDateTime($value);

                break;
        }

        return (string)$value;
    }
}
