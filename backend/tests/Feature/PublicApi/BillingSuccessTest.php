<?php

namespace Tests\Feature\PublicApi;

it('redirects to blog billing page', function () {

    $blog = blog([
        'subdomain' => 'agora',
    ]);
    $response = $this->get('/api/public/billing/success?resource_type=blog&resource_id=' . $blog->id);
    $response->assertRedirect('/console/agora/billing');

});