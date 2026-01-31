<?php declare(strict_types=1);

namespace App\Domains\Delivery;

use App\Models\Post;
use DateTimeInterface;
use Illuminate\Contracts\Encryption\DecryptException;

use function decrypt;
use function encrypt;
use function now;

class PostPreviewSecretEncryptor
{
    public static function getPreviewSecret(Post $post, ?DateTimeInterface $time = null) : string
    {
        $timestamp = ($time ?? now())->getTimestamp();
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

        $min = now()->subDays(7)->timestamp;
        if ($timestamp < $min) {
            return null;
        }

        return (int) $id;
    }
}
