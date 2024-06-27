<?php declare(strict_types=1);

namespace App\Domains\Language\Jobs;

use App\Models\BlogVariant;
use App\Models\Language;
use App\Models\NavigationVariant;
use App\Models\PostVariant;
use App\Models\TagVariant;
use App\Models\UserVariant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteLanguageVariants implements ShouldQueue, ShouldBeUnique
{
    protected Language $language;

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * Deletes all language variants after the language is deleted
     *
     * - blog
     * - posts
     * - tags
     * - users
     * - navigation
     */
    public function handle() : void
    {
        $languageId = $this->language->id;


        BlogVariant::where('language_id', $languageId)->delete();
        PostVariant::where('language_id', $languageId)->delete();
        TagVariant::where('language_id', $languageId)->delete();
        UserVariant::where('language_id', $languageId)->delete();
        NavigationVariant::where('language_id', $languageId)->delete();
    }

    public function uniqueId() : int
    {
        return $this->language->id;
    }

}
