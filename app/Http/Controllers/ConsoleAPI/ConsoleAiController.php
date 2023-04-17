<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Integrations\DeepL\DeepLPostTranslator;
use App\Domains\Integrations\DeepL\DeepLService;
use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use App\Domains\Post\Content\PostContentRepository;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleAiController
{

    public function translate(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'title' => 'string|nullable',
            'content' => 'required|string', // JSON string to translate
            'source_lang' => ['required', new Enum(DeepLSourceLangEnum::class)],
            'target_lang' => ['required', new Enum(DeepLTargetLangEnum::class)],
        ]);

        $title = (string) $request->string('title');
        $content = (string) $request->string('content');
        $sourceLang = DeepLSourceLangEnum::from((string) $request->string('source_lang'));
        $targetLang = DeepLTargetLangEnum::from((string) $request->string('target_lang'));

        $translator = new DeepLPostTranslator($blog, $content, $title, $sourceLang, $targetLang);

        [
            'title' => $translatedTitle,
            'content' => $translatedContent,
            'chars' => $chars
        ] = $translator->translate();

        return response()->json([
            'title' => $translatedTitle,
            'content' => $translatedContent,
        ]);

    }

}