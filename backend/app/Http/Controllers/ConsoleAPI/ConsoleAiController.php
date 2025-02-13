<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\Billing\UsageService;
use App\Domains\Integrations\DeepL\DeepLPostTranslator;
use App\Domains\Integrations\DeepL\DeepLService;
use App\Domains\Integrations\DeepL\Enums\DeepLSourceLangEnum;
use App\Domains\Integrations\DeepL\Enums\DeepLTargetLangEnum;
use App\Domains\Integrations\DeepL\Exceptions\DeepLApiException;
use App\Domains\Integrations\DeepL\Exceptions\DeepLHtmlProcessingException;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class ConsoleAiController
{

    public function translate(Request $request, Blog $blog, UsageService $usageService) : JsonResponse
    {

        $request->validate([
            'slug' => 'string|nullable',
            'title' => 'string|nullable',
            'description' => 'string|nullable',
            'content' => 'required|string', // JSON string to translate
            'source_lang' => ['required', new Enum(DeepLSourceLangEnum::class)],
            'target_lang' => ['required', new Enum(DeepLTargetLangEnum::class)],
        ]);

        if ($usageService->autoTranslationCharsLimitReached($blog)) {
            throw new TrustedException(
                'You have reached the limit of auto-translations for this month. Please upgrade your subscription plan.'
            );
        }

        $slug = (string) $request->string('slug');
        $title = (string) $request->string('title');
        $description = (string) $request->string('description');
        $content = (string) $request->string('content');
        $sourceLang = DeepLSourceLangEnum::from((string) $request->string('source_lang'));
        $targetLang = DeepLTargetLangEnum::from((string) $request->string('target_lang'));

        $translator = new DeepLPostTranslator(
            $blog,
            $content,
            $title,
            $description,
            $slug,
            $sourceLang,
            $targetLang
        );

        try {
            [
                'title' => $translatedTitle,
                'description' => $translatedDescription,
                'slug' => $translatedSlug,
                'content' => $translatedContent,
                'chars' => $chars
            ] = $translator->translate();
        } catch (DeepLHtmlProcessingException | DeepLApiException $e) {
            throw new TrustedException($e->getMessage());
        }

        DeepLService::addAutoTranslationRecord($blog, $sourceLang, $targetLang, $chars);

        return response()->json([
            'title' => $translatedTitle,
            'description' => $translatedDescription,
            'slug' => Str::slug($translatedSlug),
            'content' => $translatedContent,
        ]);

    }

}
