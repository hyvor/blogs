<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\RedirectTypeEnum;
use App\Data\Objects\ConsoleAPI\RedirectObject;
use App\Domains\Redirect\RedirectRepository;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Redirect;
use App\Rules\RedirectPath;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleRedirectController extends Controller
{

    public function get(Request $request, Blog $blog)
    {
         $request->validate([
             'limit' => 'integer',
             'offset' => 'integer',
         ]);

        $limit = $request->input('limit', 25);
        $offset = $request->input('offset', 0);

        $redirects = RedirectRepository::getRedirects($blog, $limit, $offset)->mapInto(RedirectObject::class);

        return response()->json($redirects);
    }

    public function create(Request $request, Blog $blog)
    {

        $request->validate([
            'path' => ['required', new RedirectPath($blog)],
            'to' => ['required', 'url'],
            'type' => ['required', new Enum(RedirectTypeEnum::class)],
        ]);
        $path = $request->input('path');
        $to = $request->input('to');
        $type = RedirectTypeEnum::from($request->input('type'));

        $redirect = RedirectRepository::createRedirect($blog, $path, $to, $type);

        return response()->json(new RedirectObject($redirect));

    }

    public function update(Request $request, Blog $blog, Redirect $redirect)
    {

        $request->validate([
            'path' => ['required', new RedirectPath($blog)],
            'to' => ['required', 'url'],
            'type' => ['required', new Enum(RedirectTypeEnum::class)],
        ]);

        $path = $request->input('path');
        $to = $request->input('to');
        $type = RedirectTypeEnum::from($request->input('type'));

        $redirect = RedirectRepository::updateRedirect($redirect, $path, $to, $type);

        return response()->json(new RedirectObject($redirect));

    }

    public function delete(Redirect $redirect)
    {

        RedirectRepository::deleteRedirect($redirect);
        return response()->json();

    }
}
