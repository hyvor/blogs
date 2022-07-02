<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\LanguageObject;
use App\Domains\Language\LanguageRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Http\Request;

class ConsoleLanguageController extends Controller
{
    public static function get(Blog $blog)
    {
        $languages = LanguageRepository::getAllLanguages($blog)
            ->map(function (Language $language) {
                return new LanguageObject($language);
            });

        return response()->json($languages);
    }

    public static function create(Request $request, Blog $blog)
    {
        $request->validate([
            'code' => 'required|string|max:12',
            'name' => 'required|string|max:255',
        ]);

        $code = $request->get('code');
        $name = $request->get('name');

        $language = LanguageRepository::getLanguageByCode($blog, $code);

        if ($language) {
            throw new TrustedException('Language already exists');
        }

        $language = LanguageRepository::createLanguage($blog, $code, $name);

        return response()->json(new LanguageObject($language));
    }

    public static function update(Request $request, Blog $blog, Language $language)
    {
        $request->validate([
            'code' => 'string|max:12',
            'name' => 'string|max:255',
        ]);

        $code = $request->input('code');
        $name = $request->input('name');

        if ($code && $code !== $language->code && LanguageRepository::getLanguageByCode($blog, $code)) {
            throw new TrustedException('Language code already exists');
        }

        $language = LanguageRepository::updateLanguage($language, $code, $name);

        return response()->json(new LanguageObject($language));
    }

    public static function delete(Language $language)
    {
        if ($language->is_primary) {
            throw new TrustedException('Primary language cannot be deleted');
        }

        LanguageRepository::deleteLanguage($language);

        return response()->json();
    }
}
