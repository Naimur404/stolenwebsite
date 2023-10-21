<?php

namespace Botble\Blog\Http\Controllers;

use Botble\ACL\Models\User;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Forms\TagForm;
use Botble\Blog\Http\Requests\TagRequest;
use Botble\Blog\Models\Tag;
use Botble\Blog\Tables\TagTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends BaseController
{
    public function index(TagTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/blog::tags.menu'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/blog::tags.create'));

        return $formBuilder->create(TagForm::class)->renderForm();
    }

    public function store(TagRequest $request, BaseHttpResponse $response)
    {
        $tag = Tag::query()->create(array_merge($request->input(), [
            'author_id' => Auth::id(),
            'author_type' => User::class,
        ]));
        event(new CreatedContentEvent(TAG_MODULE_SCREEN_NAME, $request, $tag));

        return $response
            ->setPreviousUrl(route('tags.index'))
            ->setNextUrl(route('tags.edit', $tag->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Tag $tag, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $tag->name]));

        return $formBuilder->create(TagForm::class, ['model' => $tag])->renderForm();
    }

    public function update(Tag $tag, TagRequest $request, BaseHttpResponse $response)
    {
        $tag->fill($request->input());
        $tag->save();

        event(new UpdatedContentEvent(TAG_MODULE_SCREEN_NAME, $request, $tag));

        return $response
            ->setPreviousUrl(route('tags.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Tag $tag, Request $request, BaseHttpResponse $response)
    {
        try {
            $tag->delete();

            event(new DeletedContentEvent(TAG_MODULE_SCREEN_NAME, $request, $tag));

            return $response->setMessage(trans('plugins/blog::tags.deleted'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getAllTags()
    {
        return Tag::query()->pluck('name')->all();
    }
}
