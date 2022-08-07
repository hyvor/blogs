<?php

namespace App\Domains\Integrations\Shopify\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class ShopDomainRule implements InvokableRule
{


    public function __invoke($attribute, $value, $fail)
    {

        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*.myshopify.com/', $value)) {
            $fail('Invalid shop domain');
        }

    }
}