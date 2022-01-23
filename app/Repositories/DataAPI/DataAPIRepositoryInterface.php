<?php

namespace App\Domains\DataAPI;

interface DataAPIRepositoryInterface
{
    public function post(DataAPISingleRequest $request): array;
    public function author(DataAPISingleRequest $request): array;
    public function tag(DataAPISingleRequest $request): array;

    public function posts(DataAPIMultiRequest $request): array;
    public function authors(DataAPIMultiRequest $request): array;
    public function tags(DataAPIMultiRequest $request): array;
}
