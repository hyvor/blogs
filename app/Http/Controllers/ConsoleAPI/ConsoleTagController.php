<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\Tag\TagObject;
use App\Data\Objects\ConsoleAPI\Tag\TagVariantObject;
use App\Domains\Post\PostTagRepository;
use App\Domains\Tag\TagRepository;

use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsoleTagController extends Controller
{
    /*
    *
    * ConsoleAPI Settings->tags
    *
    */
    public function get(Request $request, Blog $blog)
    {
        $request->validate([
             'limit' => 'integer',
             'offset' => 'integer',
         ]);

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $tags = TagRepository::getTags($blog, $limit, $offset)->map(fn ($tag) => new TagObject($tag, $blog));

        return response()->json($tags);
    }

    public function create(Request $request, Blog $blog)
    {
        $request->validate([
             'name' => 'required|string'
         ]);

        $name = $request->input('name');

        $tag = TagRepository::createTag($blog, $name);

        return response()->json(new TagObject($tag, $blog));
    }

    public function update(Request $request, Tag $tag, Blog $blog)
    {

        $validates = [
            'slug' => 'string',
            'code_head' => 'string|nullable',
            'code_foot' => 'string|nullable'
        ];

        $request->validate($validates);

        $updatables =  array_keys($validates);
        $updates = [];

        foreach ($updatables as $updatable) {
            if ($request->has($updatable)) {
                $updates[$updatable] = $request->input($updatable);
            }
        }

        $tag = TagRepository::updateTag($tag, $updates);

        return response()->json(new TagObject($tag, $blog));
    }

    public function delete(Tag $tag)
    {
        TagRepository::deleteTag($tag);
        return response()->json();
    }


    public function createVariant(Blog $blog, Tag $tag, Language $language)
    {
        $variant = TagRepository::createTagVariant($tag, $language);
        return response()->json(new TagVariantObject($variant, $tag, $blog));
    }

    public function updateVariant(Request $request, Blog $blog, Tag $tag, Language $language)
    {

        $variant = TagRepository::getTagVariantByTagIdAndLanguageId($tag->id, $language->id);

        if (!$variant)
            throw new TrustedException('Variant not found');

        $validates = [
            'name' => 'string|nullable',
            'description' => 'string|nullable'
        ];

        $request->validate($validates);

        $updatables =  array_keys($validates);
        $updates = [];

        foreach ($updatables as $updatable) {
            if ($request->has($updatable)) {
                $updates[$updatable] = $request->input($updatable);
            }
        }

        $variant = TagRepository::updateTagVariant($variant, $updates);

        return response()->json(new TagVariantObject($variant, $tag, $blog));

    }

    public function deleteVariant(Tag $tag, Language $language)
    {

        if ($language->is_primary) {
            throw new TrustedException(
                'Primary language variant cannot be deleted. Delete the tag instead',
                TrustedException::ERROR_UNPROCESSABLE
            );
        }

        $variant = TagRepository::getTagVariantByTagIdAndLanguageId($tag->id, $language->id);

        if (!$variant)
            throw new TrustedException('Variant not found', TrustedException::ERROR_NOT_FOUND);

        TagRepository::deleteTagVariant($variant);

        return response()->json();

    }

}
