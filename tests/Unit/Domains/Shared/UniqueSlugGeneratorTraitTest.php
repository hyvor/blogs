<?php

namespace Tests\Unit\Domains\Shared;

use App\Domains\Shared\UniqueSlugGeneratorTrait;

it('generates a unique slug', function() {

    $cls = new class {

        public function exists(string $slug): bool
        {
            return $slug === 'first';
        }

        use UniqueSlugGeneratorTrait;
    };


    $slug = (new $cls)->generateSlug(['first', 'second']);
    expect($slug)->toBe('second');

    $slug = (new $cls)->generateSlug(['first']);
    expect($slug)->toBeString()->toHaveLength(16);

});