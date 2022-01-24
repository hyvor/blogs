<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Redirect\RedirectRepository;

use App\Models\Redirect;


class ConsoleRedirectController extends Controller {

    public function getRedirect() {
        $getData = RedirectRepository::getRedirects();
        return view('test.test', ['getData' =>$getData]);
    }

    public function createRedirect(Request $req ) {
        RedirectRepository::createRedirects($req);
        return 'hello world';
    }

    public function showData($subdomain ,$id) {
        $getData = Redirect::find($id);
        return view('test.update', ['getData' =>$getData]);
    }

    public function updateRedirect(Request $req) {
        RedirectRepository::updateRedirects($req);
        return view('test.test');
    }

    public function deleteRedirect($subdomain ,$id) {
        RedirectRepository::deleteRedirects($id);
        return'delete redirect';
    }
}