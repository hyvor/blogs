<?php

namespace App\Http\Controllers\DataAPI;

use App\Data\Objects\DataAPI\AuthorObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Data\Objects\DataAPI\TagObject;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\PostSearchRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Domains\Post\PostRepository;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;
use App\Models\Language;
use Exception;

class DataAPIController extends Controller
{

    public function author(Request $request, Blog $blog)
    {
        $id = $request->input('id');
        $slug = $request->input('slug');
        $keys = $request->input('keys');

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
        ]);

        $user = UserRepository::getUserByBlogIdAndIdentifier($blog->id, $id, $slug);
        if (!$user) {
            throw new TrustedException('Tag not found', TrustedException::ERROR_NOT_FOUND);
        }
        if (!$user->posts_count > 0) {
            throw new TrustedException('This user is not an author', TrustedException::ERROR_BAD_REQUEST);
        }

        return response()->json(DataAPIKeysFilter::filter(new AuthorObject($user, $blog), $keys));
    }

    private function getLanguage(Blog $blog, ?string $code) : Language
    {

        if (is_null($code)) {
            return LanguageRepository::getPrimaryLanguage($blog);
        } else {
            $language = LanguageRepository::getLanguageByCode($blog, $code);

            if (!$language) {
                throw new TrustedException('Language not found', TrustedException::ERROR_BAD_REQUEST);
            }

            return $language;
        }

    }

}
