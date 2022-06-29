<?php

namespace App\Domains\Delivery;

use App\Models\Post;
use function decrypt;
use function encrypt;
use Illuminate\Contracts\Encryption\DecryptException;
use function now;

class PostPreviewSecretEncryptor
{
    public static function getPreviewSecret(Post $post)
    {
        $timestamp = now()->timestamp;

        return encrypt("$post->id.$timestamp");
    }

    public static function decryptPreviewSecret(string $encrypted): ?int
    {
        try {
            $decrypted = decrypt($encrypted);
        } catch (DecryptException) {
            return null;
        }

        $split = explode('.', $decrypted);

        $id = $split[0] ?? null;
        $timestamp = $split[1] ?? null;

        if (! $id || ! $timestamp) {
            return null;
        }

        $min = now()->subDay()->timestamp;
        if ($timestamp < $min) {
            return null;
        }

        return (int) $id;
    }
}
