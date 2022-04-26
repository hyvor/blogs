<?php
namespace App\Data\Objects\DeliveryAPI;


class MetaObject {

    public function __construct(
        public string $title,
        public ?string $description,
        public ?string $featured_image,
        public string $url,
        public string $canonical_url,
    ) {}

}