<?php
namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\LanguageObject;
use App\Domains\Language\LanguageRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Http\Request;

class ConsoleLanguageController extends Controller {

    public static function get(Blog $blog) {
        
        $languages = LanguageRepository::getAllLanguages($blog->id)
            ->map(function (Language $language) {
                return new LanguageObject($language);
            });

        return response()->json($languages);

    }

    public static function create(Request $request, Blog $blog) {

        $request->validate([
            'code' => 'required|string|max:12',
            'name' => 'required|string|max:255',
        ]);

        $code = $request->get('code');
        $name = $request->get('name');

        $language = LanguageRepository::createLanguage($blog->id, $code, $name);

        return response()->json( new LanguageObject($language) );

    }

    public static function update(Request $request) {

        $request->validate([
            'code' => 'required|string|max:12',
            'name' => 'required|string|max:255',
        ]);

        $id = $request->route('id');
        $code = $request->get('code');
        $name = $request->get('name');

        $language = LanguageRepository::updateLanguage($id, $code, $name);

        return response()->json( new LanguageObject($language) );
    }

    public static function delete(Request $request) {

        $id = (int) $request->route('id');

        LanguageRepository::deleteLanguage($id);
        
        return response()->json();

    }

}