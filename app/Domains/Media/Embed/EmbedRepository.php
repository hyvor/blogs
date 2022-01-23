<?php

namespace App\Domains\Media\Embed;

use App\Exceptions\TrustedException;
use App\Models\Embed;

class EmbedRepository
{
    static function fetch($url): Embed
    {

        $embed = Embed::where('url', $url)->first();

        if ($embed) {
            return $embed;
        }

        try {
            $embed = Iframely::fetch($url);

            return Embed::create([
                'url' => $embed->url,
                'type' => $embed->type,
                'html' => $embed->html,
                'title' => $embed->title,
                'description' => $embed->description,
                'thumbnail' => $embed->thumbnail
            ]);
        } catch (IframelyException) {
            // insert to database before sending response so that we don't make multiple requests to
            // iframely endpoint for error URLs

            return Embed::create([
                'url' => $url,
                'type' => 'error'
            ]);

            throw new TrustedException('Unable to fetch');
        }
    }
}
