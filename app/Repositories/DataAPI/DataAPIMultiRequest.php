<?php

namespace App\Domains\DataAPI;

class DataAPIMultiRequest
{
    private $limit;
    private $page;
    private $filter;
    private $sort;
    private $keys;

    /**
     *
     * @return mixed
     */
    function getLimit()
    {
        return $this->limit;
    }

    /**
     *
     * @param mixed $limit
     * @return DataAPIMultiPropType
     */
    function setLimit($limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    function getPage()
    {
        return $this->page;
    }

    /**
     *
     * @param mixed $page
     * @return DataAPIMultiPropType
     */
    function setPage($page): self
    {
        $this->page = $page;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    function getFilter()
    {
        return $this->filter;
    }

    /**
     *
     * @param mixed $filter
     * @return DataAPIMultiPropType
     */
    function setFilter($filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    function getSort()
    {
        return $this->sort;
    }

    /**
     *
     * @param mixed $sort
     * @return DataAPIMultiPropType
     */
    function setSort($sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    function getKeys()
    {
        return $this->keys;
    }

    /**
     *
     * @param mixed $keys
     * @return DataAPIMultiPropType
     */
    function setKeys($keys): self
    {
        $this->keys = $keys;
        return $this;
    }
}
