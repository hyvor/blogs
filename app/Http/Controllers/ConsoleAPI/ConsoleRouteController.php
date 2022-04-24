<?PHP
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Route\RouteRepository;
use App\Data\Objects\ConsoleAPI\RouteObject;
use App\Models\Blog;

class ConsoleRouteController extends Controller {


    public function getRoutes(Request $request, Blog $blog) {

        $getData = RouteRepository::getRoutes($blog)
                ->map(function ($route) {
                return new RouteObject($route);
            });
        return response()->json($getData);
    }

    public function createRoute(Request $request, Blog $blog) {

        $name = $request->input('name');
        $match = $request->input('match');
        $template = $request->input('template');
        $postsFilter = $request->input('postsFilter') ?? null;
        $contentType = $request->input('contentType') ?? null;

        $route = RouteRepository::createRoute($blog, $name, $match, $template, $postsFilter, $contentType); 
        return response()->json(new RouteObject($route, $blog));
    }

    public function updateRoute(Request $request, Blog $blog) {

        $id = $request->route('id');
        $name = $request->input('name');
        $match = $request->input('match');
        $template = $request->input('template');
        $postsFilter = $request->input('postsFilter') ?? null;
        $contentType = $request->input('contentType') ?? null;
        
        $route = RouteRepository::updateRoute($id, $name, $match, $template, $postsFilter, $contentType );
        return response()->json($route);
    }

    public function deleteRoute(Request $request) {

        $id = $request->route('id');
        $deleteRoute = RouteRepository::deleteRoute($id);
        return response()->json($deleteRoute);
    }
}
