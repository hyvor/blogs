<?php

namespace App\Service\Post;

use App\Entity\Language;
use App\Entity\PostVariant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PostSlugService
{
    public function __construct(private EntityManagerInterface $em) {}


    private const SLUG_INVALID_CHARACTERS = [
        ':',
        '/',
        '?',
        '#',
        '[',
        ']',
        '@',
        '!',
        '$',
        '&',
        "'",
        '(',
        ')',
        '*',
        '+',
        ',',
        ';',
        '=',
        '%',
    ];

    public function validateSlug(string $slug): ?string
    {
        // might be better to 
        foreach (self::SLUG_INVALID_CHARACTERS as $char) {
            if (str_contains($slug, $char)) {
                return $char;
            }
        }
        return null;
    }

    /**
     * Generates a slug from hint (typically post title), ensuring it's unique for the given language.
     */
    public function generateUniqueSlug(Language $language, ?string $hint = null): string
    {
        $slugger = new AsciiSlugger();
        $base = $hint ? strtolower((string)$slugger->slug($hint)) : '';
        $candidate = $base ?: bin2hex(random_bytes(8));

        $existing = $this->em->getRepository(PostVariant::class)->findOneBy([
            'language' => $language,
            'slug' => $candidate,
        ]);

        if ($existing === null) {
            return $candidate;
        }

        return bin2hex(random_bytes(8));
    }
}
