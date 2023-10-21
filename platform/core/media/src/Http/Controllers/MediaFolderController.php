<?php

namespace Botble\Media\Http\Controllers;

use Botble\Media\Facades\RvMedia;
use Botble\Media\Http\Requests\MediaFolderRequest;
use Botble\Media\Models\MediaFolder;
use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * @since 19/08/2015 07:55 AM
 */
class MediaFolderController extends Controller
{
    public function store(MediaFolderRequest $request)
    {
        try {
            $name = $request->input('name');
            $parentId = $request->input('parent_id');

            MediaFolder::query()->create([
                'name' => MediaFolder::createName($name, $parentId),
                'slug' => MediaFolder::createSlug($name, $parentId),
                'parent_id' => $parentId,
                'user_id' => Auth::id(),
            ]);

            return RvMedia::responseSuccess([], trans('core/media::media.folder_created'));
        } catch (Exception $exception) {
            return RvMedia::responseError($exception->getMessage());
        }
    }
}
