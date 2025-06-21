<?php

namespace App\Console\Commands\Migrations;

use App\Domains\Post\FullTextSearchService;
use App\Models\PostVariant;
use Illuminate\Console\Command;

class D_2025_01_22_PostVariantTsLanguage extends Command
{

    public $name = 'migrate:post-variant-ts-language';

    public function handle(): void
    {
        PostVariant::orderBy('id')->chunk(100, function ($postVariants) {
            $this->info('From ' . $postVariants->first()?->id . ' to ' . $postVariants->last()?->id);

            $fts = new FullTextSearchService();

            foreach ($postVariants as $postVariant) {
                $tsLanguage = $fts->findClosestRegconfigByLanguageCode($postVariant->language->code);
                $postVariant->update([
                    'ts_language' => $tsLanguage
                ]);
            }
        });
    }

}
