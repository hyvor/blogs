<?php

namespace App\Service\Blog\UpdateBlogUrls\Updater;

interface UpdaterInterface {

    /**
     * @return false|string false for no change, or the updated URL if changed
     */
    public function update(string $url): false|string;

}
