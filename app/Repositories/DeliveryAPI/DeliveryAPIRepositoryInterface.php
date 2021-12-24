<?php
/**
 * Created by PhpStorm.
 * User: LENOVO
 * Date: 2021-06-30
 * Time: 11:20 AM
 */

namespace App\Repositories\DeliveryAPI;

interface DeliveryAPIRepositoryInterface
{
    public function getUrl($geturl, $request);

    // public function index();
    // public function author($getAuthor);
    // public function tag($getTag);
    // public function pages($getPage);
}

