<?php

namespace Botble\Blog\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Blog\Exports\PostExport;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class PostTable extends TableAbstract
{
    protected string $exportClass = PostExport::class;

    protected int $defaultSortColumn = 6;

    public function setup(): void
    {
        $this
            ->model(Post::class)
            ->addActions([
                EditAction::make()->route('posts.edit'),
                DeleteAction::make()->route('posts.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('categories_name', function (Post $item) {
                $categories = [];
                foreach ($item->categories as $category) {
                    $categories[] = Html::link(route('categories.edit', $category->id), $category->name);
                }

                return implode(', ', $categories);
            })
            ->editColumn('author_id', function (Post $item) {
                return $item->author && $item->author->name ? BaseHelper::clean($item->author->name) : '&mdash;';
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->with([
                'categories' => function (BelongsToMany $query) {
                    $query->select(['categories.id', 'categories.name']);
                },
                'author',
            ])
            ->select([
                'id',
                'name',
                'image',
                'created_at',
                'status',
                'updated_at',
                'author_id',
                'author_type',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            ImageColumn::make(),
            NameColumn::make()->route('posts.edit'),
            Column::make('categories_name')
                ->title(trans('plugins/blog::posts.categories'))
                ->width(150)
                ->orderable(false)
                ->searchable(false),
            Column::make('author_id')
                ->title(trans('plugins/blog::posts.author'))
                ->width(150)
                ->orderable(false)
                ->searchable(false),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('posts.create'), 'posts.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('posts.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'customSelect',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'category' => [
                'title' => trans('plugins/blog::posts.category'),
                'type' => 'select-search',
                'validate' => 'required|string',
                'callback' => 'getCategories',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
                'validate' => 'required|string|date',
            ],
        ];
    }

    public function getCategories(): array
    {
        return Category::query()->pluck('name', 'id')->all();
    }

    public function applyFilterCondition(
        EloquentBuilder|QueryBuilder|EloquentRelation $query,
        string $key,
        string $operator,
        string|null $value
    ): EloquentRelation|EloquentBuilder|QueryBuilder {
        if ($key === 'category' && $value) {
            return $query->whereHas('categories', fn (BelongsToMany $query) => $query->where('categories.id', $value));
        }

        return parent::applyFilterCondition($query, $key, $operator, $value);
    }

    public function saveBulkChangeItem(Model|Post $item, string $inputKey, string|null $inputValue): Model|bool
    {
        if ($inputKey === 'category' && $item instanceof Post) {
            $item->categories()->sync([$inputValue]);

            return $item;
        }

        return parent::saveBulkChangeItem($item, $inputKey, $inputValue);
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}
