<?php

namespace Botble\Blog\Http\Controllers;

use Botble\ACL\Models\User;
use Botble\Base\Events\BeforeUpdateContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Forms\PostForm;
use Botble\Blog\Http\Requests\PostRequest;
use Botble\Blog\Models\Post;
use Botble\Blog\Services\StoreCategoryService;
use Botble\Blog\Services\StoreTagService;
use Botble\Blog\Tables\PostTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends BaseController
{
    public function index(PostTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/blog::posts.menu_name'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/blog::posts.create'));

        return $formBuilder->create(PostForm::class)->renderForm();
    }

    public function store(
        PostRequest $request,
        StoreTagService $tagService,
        StoreCategoryService $categoryService,
        BaseHttpResponse $response
    ) {
        $post = Post::query()->create(
            array_merge($request->input(), [
                'author_id' => Auth::id(),
                'author_type' => User::class,
            ])
        );

        event(new CreatedContentEvent(POST_MODULE_SCREEN_NAME, $request, $post));

        $request->request->remove('seo_meta');

        $tagService->execute($request, $post);

        $categoryService->execute($request, $post);

        return $response
            ->setPreviousUrl(route('posts.index'))
            ->setNextUrl(route('posts.edit', $post->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Post $post, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $post->name]));

        return $formBuilder->create(PostForm::class, ['model' => $post])->renderForm();
    }

    public function update(
        Post $post,
        PostRequest $request,
        StoreTagService $tagService,
        StoreCategoryService $categoryService,
        BaseHttpResponse $response
    ) {
        event(new BeforeUpdateContentEvent($request, $post));

        $post->fill($request->input());
        $post->save();

        event(new UpdatedContentEvent(POST_MODULE_SCREEN_NAME, $request, $post));

        $tagService->execute($request, $post);

        $categoryService->execute($request, $post);

        return $response
            ->setPreviousUrl(route('posts.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Post $post, Request $request, BaseHttpResponse $response)
    {
        try {
            $post->delete();

            event(new DeletedContentEvent(POST_MODULE_SCREEN_NAME, $request, $post));

            return $response
                ->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getWidgetRecentPosts(Request $request, BaseHttpResponse $response)
    {
        $limit = $request->integer('paginate', 10);
        $limit = $limit > 0 ? $limit : 10;

        $posts = Post::query()
            ->with(['slugable'])
            ->orderByDesc('created_at')
            ->paginate($limit);

        return $response
            ->setData(view('plugins/blog::posts.widgets.posts', compact('posts', 'limit'))->render());
    }
}
