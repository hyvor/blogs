<?php

namespace App\Tests\Service\Post;

use App\Service\Post\PostSlugService;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostSlugService::class)]
class PostSlugServiceTest extends KernelTestCase
{

    public function test_validate_slug(): void
    {
        $slugValidationService = $this->getService(PostSlugService::class);

        $this->assertNull($slugValidationService->validateSlug('valid-slug'));
        $this->assertSame(':', $slugValidationService->validateSlug('invalid:slug'));
        $this->assertSame('/', $slugValidationService->validateSlug('invalid/slug'));
        $this->assertSame('?', $slugValidationService->validateSlug('invalid?slug'));
        $this->assertSame('#', $slugValidationService->validateSlug('invalid#slug'));
        $this->assertSame('[', $slugValidationService->validateSlug('invalid[slug'));
        $this->assertSame(']', $slugValidationService->validateSlug('invalid]slug'));
        $this->assertSame('@', $slugValidationService->validateSlug('invalid@slug'));
        $this->assertSame('!', $slugValidationService->validateSlug('invalid!slug'));
        $this->assertSame('$', $slugValidationService->validateSlug('invalid$slug'));
        $this->assertSame('&', $slugValidationService->validateSlug('invalid&slug'));
        $this->assertSame("'", $slugValidationService->validateSlug("invalid'slug"));
        $this->assertSame('(', $slugValidationService->validateSlug('invalid(slug'));
        $this->assertSame(')', $slugValidationService->validateSlug('invalid)slug'));
        $this->assertSame('*', $slugValidationService->validateSlug('invalid*slug'));
        $this->assertSame('+', $slugValidationService->validateSlug('invalid+slug'));
        $this->assertSame(',', $slugValidationService->validateSlug('invalid,slug'));
        $this->assertSame(';', $slugValidationService->validateSlug('invalid;slug'));
        $this->assertSame('=', $slugValidationService->validateSlug('invalid=slug'));
        $this->assertSame('%', $slugValidationService->validateSlug('invalid%slug'));
    }

    public function test_generate_unique_slug(): void
    {
        $slugService = $this->getService(PostSlugService::class);
        $language = LanguageFactory::createOne(['name' => 'English', 'code' => 'en']);
        PostVariantFactory::createOne([
            'language' => $language,
            'slug' => 'test-post',
        ]);
        $slug1 = $slugService->generateUniqueSlug($language, 'Test Post');
        $this->assertNotSame('test-post', $slug1);
    }

}
