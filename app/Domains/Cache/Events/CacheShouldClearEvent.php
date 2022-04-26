<?php
namespace App\Domains\Cache\Events;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use App\Domains\Delivery\DeliveryRepository;
use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CacheShouldClearEvent 
{

    use Dispatchable, SerializesModels;

    /**
     * A path
     */
    public string $path;

    /**
     * A blog
     */
    public Blog $blog;

    /**
     * Response object from Delivery API
     * So that we can send this in webhooks
     */
    public DeliveryAPIResponseObject $responseObject;

    /**
     * $path = path to clear
     */
    public function __construct(Blog $blog, string $path)
    {

        $this->blog = $blog;
        $this->path = $path;
        $this->responseObject = DeliveryRepository::getResponseObject($blog, $path);

    }

}