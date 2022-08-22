<?php

namespace App\Domains\Integrations\Github\Webhook;

use App\Exceptions\TrustedException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WebhookValidator
{


    public static function validate(Request $request, string $secret) : bool
    {

        $signature = $request->header('X-Hub-Signature-256');

        if (!$signature) {
            throw new BadRequestHttpException('Github Signature 256 not defined');
        }

        $parts = explode('=', $signature);

        if (count($parts) !== 2) {
            throw new BadRequestHttpException('Invalid signature format');
        }

        $knownHash = hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($knownHash, $parts[1])) {
            throw new TrustedException('Count not verify the signature');
        }

        return true;

    }

}