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

    public function __construct($facebook, $twitter, $linkedin, $youtube, $instagram, $github)
    {
        $this->facebook = $facebook;
        $this->twitter = $twitter;
        $this->linkedin = $linkedin;
        $this->youtube = $youtube;
        $this->instagram = $instagram;
        $this->github = $github;
    }
}
