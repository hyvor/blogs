<?php

namespace App\Service;

class Limit
{
    const int MAX_MEDIA_UPLOAD_SIZE = 50 * 1000 * 1000;  // 50MB in bytes
    const int MAX_THEME_ZIP_SIZE    = 50 * 1000 * 1000;  // 50MB in bytes
    const int MAX_ASSET_FILE_SIZE   =  2 * 1000 * 1000;  //  2MB in bytes
}
