<?php
namespace App\Http\Controllers\DataAPI;

use App\Data\Objects\DataAPI\TagObject;
use App\Domains\Tag\TagRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class TagsController extends Controller
{

    public function tag(Request $request, Blog $blog)
    {

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
            'language' => 'string'
        ]);

        $id = $request->input('id');
        $slug = $request->input('slug');
        $language = Helper::getLanguage($blog, $request->input('language'));
        $keys = $request->input('keys');
        
        $tag = TagRepository::getTagByBlogIdAndIdentifier($blog->id, $id, $slug);

        if (!$tag) {
            throw new TrustedException('Tag not found', TrustedException::ERROR_NOT_FOUND);
        }

        return response()->json(
            KeysFilter::filter(new TagObject($tag, $blog, $language), $keys)
        );

    }

    public function tags(Request $request, Blog $blog)
    {
        
        

    }

}
