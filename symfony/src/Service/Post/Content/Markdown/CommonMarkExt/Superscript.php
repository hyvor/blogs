<?php declare(strict_types=1);

namespace App\Service\Post\Content\Markdown\CommonMarkExt;

use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Node\Inline\DelimitedInterface;

/**
 * Inline node for `^text^` (the "sup" mark), matching what
 * MarkdownSerializer emits for the sup mark. CommonMark has no
 * built-in delimiter for `^`, so we register one ourselves.
 */
final class Superscript extends AbstractInline implements DelimitedInterface
{
    public function __construct(private readonly string $delimiter = '^')
    {
        parent::__construct();
    }

    public function getOpeningDelimiter(): string
    {
        return $this->delimiter;
    }

    public function getClosingDelimiter(): string
    {
        return $this->delimiter;
    }
}
