<?php

namespace Tests\Unit\Delivery\PathMatcher;

use App\Domains\Delivery\PathMatcher;
use App\Domains\Language\LanguageRepository;

it('correctly sets the language', function () {

    $blog = blog();
    addPrimaryLanguage($blog);
    addLanguage($blog);

    $languages = $blog->languages;

    // EN
    $pathMatcherDefaultLang = new PathMatcher($blog, '/');
    $languageDefault = $pathMatcherDefaultLang->language;

    $this->assertEquals($languages[0]->id, $languageDefault->id);

    // FR
    $pathMatcherSecondary = new PathMatcher($blog, "/{$languages[1]->code}/hello-world");
    $languageSecondary = $pathMatcherSecondary->language;

    $this->assertEquals($languages[1]->id, $languageSecondary->id);
    $this->assertEquals('/hello-world', $pathMatcherSecondary->path); // language code should be removed

    // Invalid (Should fallback to default)
    $pathMatcherInvalid = new PathMatcher($blog, '/jp/hello-world');
    $languageInvalid = $pathMatcherInvalid->language;

    $this->assertEquals($languages[0]->id, $languageInvalid->id);
    $this->assertEquals('/jp/hello-world', $pathMatcherInvalid->path); // language code should NOT be removed

    // fr-FR
    $langWithCountry = LanguageRepository::createLanguage($blog, 'fr-FR', 'French (France)');
    $blog->refresh();
    $pathMatcher = new PathMatcher($blog, '/fr-FR/hello-world');
    $pathMatcherLanguageWithCountry = $pathMatcher->language;

    $this->assertEquals($langWithCountry->id, $pathMatcherLanguageWithCountry->id);
    $this->assertEquals('/hello-world', $pathMatcher->path);
});
