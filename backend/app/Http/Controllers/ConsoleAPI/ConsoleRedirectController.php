<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\RedirectTypeEnum;
use App\Data\Objects\ConsoleAPI\RedirectObject;
use App\Domains\Redirect\RedirectRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Redirect;
use App\Rules\RedirectPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleRedirectController extends Controller
{
    public function get(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'limit' => 'integer',
            'offset' => 'integer',
        ]);

        $limit = $request->integer('limit', 25);
        $offset = $request->integer('offset', 0);

        $redirects = RedirectRepository::getRedirects(
            $blog,
            $limit,
            $offset
        )->mapInto(RedirectObject::class);

        return response()->json($redirects);
    }

    public function create(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'dynamic' => ['required', 'boolean'],
            'path' => ['required', new RedirectPath($blog)],
            'to' => ['required', 'url'],
            'type' => ['required', new Enum(RedirectTypeEnum::class)],
        ]);
        $dynamic = (bool) $request->boolean('dynamic');
        $path = (string) $request->string('path');
        $to = (string) $request->string('to');
        $type = RedirectTypeEnum::from((string) $request->string('type'));

        if (RedirectRepository::hasRedirectForPath($blog, $path)) {
            throw new TrustedException('Redirect already exists for path');
        }

        $redirect = RedirectRepository::createRedirect($blog, $dynamic, $path, $to, $type);

        return response()->json(new RedirectObject($redirect));
    }

    public function update(Request $request, Blog $blog, Redirect $redirect) : JsonResponse
    {
        sleep(1);
        $request->validate([
            'dynamic' => ['boolean'],
            'path' => [new RedirectPath($blog)],
            'to' => ['url'],
            'type' => [new Enum(RedirectTypeEnum::class)],
        ]);

        $updates = [];

        if ($request->has('dynamic')) {
            $updates['dynamic'] = (bool) $request->boolean('dynamic');
        }

        if ($request->has('path')) {
            $path = (string) $request->string('path');

            if ($path !== $redirect->path) {
                if (RedirectRepository::hasRedirectForPath($blog, $path)) {
                    throw new TrustedException('Redirect already exists for path');
                }
                $updates['path'] = $path;
            }
        }

        if ($request->has('to')) {
            $updates['to'] = (string) $request->string('to');
        }

        if ($request->has('type')) {
            $updates['type'] = RedirectTypeEnum::from((string) $request->string('type'));
        }

        $redirect = RedirectRepository::updateRedirect($redirect, $updates);

        return response()->json(new RedirectObject($redirect));
    }

    public function delete(Redirect $redirect) : JsonResponse
    {
        RedirectRepository::deleteRedirect($redirect);

        return response()->json();
    }
}
