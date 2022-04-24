<?php
namespace App\Domains\Redirect;

use App\Data\Enums\RedirectTypeEnum;
use App\Models\Blog;
use App\Models\Redirect;
use Illuminate\Support\Collection;

Class RedirectRepository
{
    public static function getRedirects( int $blogId, ?int $limit, int $offset = 0) : Collection
    {
        $limit = $limit ?? 50;
        $redirect =  Redirect::where('blog_id','=', $blogId)
            ->limit($limit)
            ->offset($offset)
            ->latest()
            ->get(); 
            
        return $redirect;
    } 

    public static function createRedirect(
        int $blogId, string $path, string $to, $type
    ) : Redirect
    {
        return Redirect::create([
            'blog_id' => $blogId,
            'path' => $path,
            'to' => $to,
            'type' => $type,
        ]);
    }

    public static function updateRedirect(int $blogId ,int $id, string $path, string $to, $type) : void
    {
        $redirect = Redirect::find($id);
        $redirect->path=$path;
        $redirect->to=$to;
        $redirect->type=$type;

        $redirect->save();
    }

    public static function deleteRedirect(int $id) : void
    {
        $data = Redirect::find($id);
        $data->delete();
    }

    /**
     * TODO: Update this to match wildcards
     */
    public static function findRedirectForPath(Blog $blog, string $path) : Redirect|null
    {
        return $blog->redirects()
            ->where('path', $path)
            ->first();
    }

}


