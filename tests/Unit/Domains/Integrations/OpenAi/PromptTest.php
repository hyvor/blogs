<?php

namespace Tests\Unit\Domains\Integrations\OpenAi;

use App\Domains\Integrations\OpenAi\Prompt;

it('works', function() {

    $prompt = new Prompt(null, 'test');
    $response = $prompt->getResponse();

    dd($response);

});