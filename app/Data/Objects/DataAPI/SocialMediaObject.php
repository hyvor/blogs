<?php

namespace App\Data\Objects\DataAPI;

class SocialMediaObject
{
    public ?string $facebook;
    public ?string $twitter;
    public ?string $linkedin;
    public ?string $youtube;
    public ?string $instagram;
    public ?string $github;
    public ?string $tiktok;

    public function __construct(
        ?string $facebook,
        ?string $twitter,
        ?string $linkedin,
        ?string $youtube,
        ?string $instagram,
        ?string $github,
        ?string $tiktok
    ) {
        $this->facebook = 'https://twitter.com'; // $facebook;
        $this->twitter = 'https://twitter.com'; // $twitter;
        $this->linkedin = $linkedin;
        $this->youtube = $youtube;
        $this->instagram = $instagram;
        $this->github = $github;
        $this->tiktok = $tiktok;
    }
}
