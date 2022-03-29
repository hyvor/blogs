<?php

namespace App\Data\Params\ConsoleAPI;

use App\Exceptions\TrustedException;

/**
 * Filter object for /posts endpoint in the console
 * Used in PostRepository
 */
class PostsFilterParam
{
    public $status;
    public $authorId;
    public $tagId;
    public $languageId;
    public $startTimestamp;
    public $endTimestamp;
    public $search;

    public function setStatus(?string $status)
    {
        if (!in_array($status, [null, 'published', 'draft', 'scheduled', 'featured'])) {
            throw new TrustedException('Invalid status for filteing: ' . $status);
        }
        $this->status = $status;
        return $this;
    }

    public function setAuthorId(?int $authorId)
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function setTagId(?int $tagId)
    {
        $this->tagId = $tagId;
        return $this;
    }

    public function setStartTimestamp(?int $startTimestamp)
    {
        $this->startTimestamp = $startTimestamp;
        return $this;
    }

    public function setEndTimestamp(?int $endTimestamp)
    {
        $this->endTimestamp = $endTimestamp;
        return $this;
    }

    public function setSearch(?string $search)
    {
        $this->search = $search;
        return $this;
    }

    public function setLanguageId(?int $languageId) {
        $this->languageId = $languageId;
        return $this;
    }

}
