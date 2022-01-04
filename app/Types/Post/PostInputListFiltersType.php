<?php
namespace App\Types\Post;

use App\Exceptions\TrustedException;

/**
 * Filter object for /posts endpoint in the console
 * Used in PostRepository
 */
class PostInputListFiltersType {

    public $status;
    public $authorId;
    public $tagId;
    public $startTimestamp;
    public $endTimestamp;
    public $search;

    public function setStatus(?string $status) {
        if (is_null($status)) {
            $status = 'all';
        }
        if (!in_array($status, ['all', 'published', 'deleted', 'draft', 'scheduled'])) {
            throw new TrustedException('Invalid status for filteing: ' . $status);
        }
        $this->status = $status;
        return $this;
    }

    public function setAuthorId(?int $authorId) {
        $this->authorId = $authorId;
        return $this;
    }

    public function setTagId(?int $tagId) {
        $this->tagId = $tagId;
        return $this;
    }

    public function setStartTimestamp(?int $startTimestamp) {
        $this->startTimestamp = $startTimestamp;
        return $this;
    }

    public function setEndTimestamp(?int $endTimestamp) {
        $this->endTimestamp = $endTimestamp;
        return $this;
    }

    public function setSearch(?string $search) {
        $this->search = $search;
        return $this;
    }

}