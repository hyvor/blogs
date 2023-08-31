<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\LanguageDirectionEnum;
use App\Data\Objects\ConsoleAPI\LanguageObject;
use App\Domains\Language\LanguageRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleLanguageController extends Controller
{
    public static function get(Blog $blog) : JsonResponse
    {
        $languages = LanguageRepository::getAllLanguages($blog)
            ->mapInto(LanguageObject::class);

        return response()->json($languages);
    }

    public static function create(Request $request, Blog $blog) : JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:12',
            'name' => 'required|string|max:255',
            'direction' => new Enum(LanguageDirectionEnum::class)
        ]);

        $code = (string) $request->string('code');
        $name = (string) $request->string('name');
        $direction = $request->has('direction') ?
            LanguageDirectionEnum::from((string) $request->string('direction')) :
            LanguageDirectionEnum::LTR;

        $language = LanguageRepository::getLanguageByCode($blog, $code);

        if ($language) {
            throw new TrustedException('Language already exists');
        }

        $language = LanguageRepository::createLanguage($blog, $code, $name, $direction);

        return response()->json(new LanguageObject($language));
    }

    public static function update(Request $request, Blog $blog, Language $language) : JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:12',
            'name' => 'required|string|max:255',
            'direction' => new Enum(LanguageDirectionEnum::class),
        ]);

        $code = (string) $request->string('code');
        $name = (string) $request->string('name');
        $direction = $request->has('direction') ?
            LanguageDirectionEnum::from((string) $request->string('direction')) :
            LanguageDirectionEnum::LTR;

        if ($code && $code !== $language->code && LanguageRepository::getLanguageByCode($blog, $code)) {
            throw new TrustedException('Language code already exists');
        }

        $language = LanguageRepository::updateLanguage($language, $code, $name, $direction);

        return response()->json(new LanguageObject($language));
    }

    public static function delete(Language $language) : JsonResponse
    {
        if ($language->is_primary) {
            throw new TrustedException('Primary language cannot be deleted');
        }

        LanguageRepository::deleteLanguage($language);

        return response()->json();
    }
}
