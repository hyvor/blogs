<?php

namespace App\Domains\Integrations\Paddle;

use App\Exceptions\TrustedException;
use Illuminate\Http\Request;

/**
 * @see https://github.com/laravel/cashier-paddle/blob/16fc919908294a884e596d2a2c549b11077bf4a4/src/Http/Middleware/VerifyWebhookSignature.php
 * @see https://developer.paddle.com/webhook-reference/ZG9jOjI1MzUzOTg2-verifying-webhooks
 */
class VerifyWebhookSignature
{
    public const SIGNATURE_KEY = 'p_signature';

    public static function verify(Request $request) : void
    {
        $fields = self::extractFields($request);
        $signature = $request->get(self::SIGNATURE_KEY);

        if (self::isInvalidSignature($fields, $signature)) {
            throw new TrustedException('Invalid webhook signature.');
        }
    }

    /**
     * @return string[]
     */
    private static function extractFields(Request $request): array
    {
        $fields = $request->except(self::SIGNATURE_KEY);

        ksort($fields);

        foreach ($fields as $k => $v) {
            if (! in_array(gettype($v), ['object', 'array'])) {
                $fields[$k] = "$v";
            }
        }

        return $fields;
    }

    /**
     * @param string[] $fields
     */
    private static function isInvalidSignature(array $fields, string $signature): bool
    {
        $publicKey = openssl_get_publickey(config('services.paddle.public_key'));
        if (!$publicKey) {
            throw new TrustedException('Failed to get the public key.');
        }
        return openssl_verify(
            serialize($fields),
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA1
        ) !== 1;
    }
}
