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
    public function getUrl($geturl);
    public function copyTheme($theme_id);
}

