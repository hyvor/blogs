<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Redirect\RedirectRepository;

use App\Models\Redirect;
use App\Models\Blog;



class ConsoleRedirectController extends Controller {

    public function getRedirects(Blog $blog) {
        $getData = RedirectRepository::getRedirects($blog->id);
        return response()->json($getData);
        
        // return view('test.test', ['getData' =>$getData]);
    }

    public function createRedirect(Request $request , Blog $blog) {
        $request->validate([
            'old_url' => 'string',
            'new_url' => 'string',
            'type' => 'int',
        ]);

        $oldURL = $request->input('old_url');
        $newURL = $request->input('new_url');
        $type = $request->input('type');

        $createRedirect = RedirectRepository::createRedirect($blog->id, $oldURL, $newURL, $type);

        return response()->json($createRedirect);

        // return 'hello world';
    }

    public function showData($subdomain ,$id) {
        $getData = Redirect::find($id);
        return view('test.update', ['getData' =>$getData]);
    }

    public function updateRedirect(Request $request, Blog $blog) {

        $request->validate([
            'old_url' => 'string',
            'new_url' => 'string',
            'type' => 'int',
        ]);

        $id = $request->route('id');
        // $oldURL = $request->input('old_url');
        // $newURL = $request->input('new_url');
        // $type = $request->input('type');

        $oldURL = 'testing whether the old url is working';
        $newURL = 'testing whether the new url is working';
        $type = '301';

        $updateRedirect = RedirectRepository::updateRedirect($blog->id, $id, $oldURL, $newURL, $type);
        // return 'update has been done';

        return response()->json($updateRedirect);


    }

    public function deleteRedirect(Request $request) {
        $id = $request->route('id');
        $deleteRedirect = RedirectRepository::deleteRedirect($id);
        // return'delete redirect';

        return response()->json($deleteRedirect);

    }
}