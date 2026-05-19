<?php

namespace App\Service;

class Limit
{
    const int MAX_MEDIA_UPLOAD_SIZE = 50 * 1000 * 1000;  // 50MB in bytes
    const int MAX_THEME_ZIP_SIZE    = 50 * 1000 * 1000;  // 50MB in bytes
    const int MAX_ASSET_FILE_SIZE   =  2 * 1000 * 1000;  //  2MB in bytes

    const int MAX_WEBHOOKS_PER_BLOG = 5;
    const int MAX_API_KEYS_PER_BLOG = 50;
    const int MAX_NAVIGATIONS_PER_TYPE = 10;
    const int MAX_REDIRECTS_PER_BLOG = 1000;
    const int MAX_ROUTES_PER_BLOG = 50;
}
