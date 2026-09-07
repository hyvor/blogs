<?php

namespace App\Api\Data\Object;

class PaginationObject
{
    public int $total;
    public int $pages;
    public int $limit;
    public int $page;
    public ?int $page_prev;
    public ?int $page_next;

    public function __construct(int $limit, int $page, int $total)
    {
        $this->total = $total;
        $this->limit = $limit;
        $this->page = $page;
        $this->pages = max((int) ceil($total / $limit), 1);
        $this->page_prev = $page > 1 ? $page - 1 : null;
        $this->page_next = $page < $this->pages ? $page + 1 : null;
    }
}
