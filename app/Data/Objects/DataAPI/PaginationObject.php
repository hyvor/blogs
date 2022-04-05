<?php
namespace App\Data\Objects\DataAPI;

class PaginationObject
{

    public int $total;
    public int $pages;
    public int $limit;
    public int $page;

    public function __construct(int $limit, int $page, int $total)
    {

        $this->total = $total;
        $this->pages = ceil($total / $limit);
        $this->limit = $limit;
        $this->page = $page;

    }

}