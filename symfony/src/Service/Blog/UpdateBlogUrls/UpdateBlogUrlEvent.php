<?php

namespace App\Service\Blog\UpdateBlogUrls;

enum UpdateBlogUrlEvent {

    /**
     * Blog's hosting URL changed, update all media and local links
     */
    case HOSTING_CHANGED;

    /**
     * A media URL was changed, update all post variants to use the new media URL
     */
    case MEDIA_URL_CHANGED;

}
