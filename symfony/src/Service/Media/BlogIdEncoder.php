<?php

namespace App\Service\Media;

/**
 * Encodes a blog ID into the short, URL-safe segment used in media URLs:
 * {mediaBaseUrl}/{blogIdEncoded}/{mediaName}
 *
 * The ID is written as a minimal big-endian byte string and encoded with
 * base64url without padding (RFC 4648 §5). Ex: 1 => "AQ", 1234 => "BNI".
 *
 * decode() only accepts the canonical encoding, so each blog has exactly one media URL prefix.
 */
final class BlogIdEncoder
{

    public static function encode(int $id): string
    {
        if ($id < 1) {
            throw new \InvalidArgumentException('Blog ID must be a positive integer');
        }

        $bytes = ltrim(pack('J', $id), "\0");
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public static function decode(string $encoded): ?int
    {
        // 8 bytes (max int) = 11 base64 characters
        if (!preg_match('/^[A-Za-z0-9_-]{2,11}$/', $encoded)) {
            return null;
        }

        $bytes = base64_decode(strtr($encoded, '-_', '+/'), true);
        if ($bytes === false || $bytes === '' || strlen($bytes) > 8) {
            return null;
        }

        /** @var array{1: int} $unpacked */
        $unpacked = unpack('J', str_pad($bytes, 8, "\0", STR_PAD_LEFT));
        $id = $unpacked[1];

        // rejects negative (overflowed) IDs and non-canonical forms (leading zero bytes, unused trailing bits)
        if ($id < 1 || self::encode($id) !== $encoded) {
            return null;
        }

        return $id;
    }

}
