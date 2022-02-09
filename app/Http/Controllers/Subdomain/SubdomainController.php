<?php

namespace App\Http\Controllers\Subdomain;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\Delivery\DeliveryRepository;
use App\Helpers\InternalAPICaller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Blog;

class SubdomainController extends Controller
{

    public function handle(Request $request, Blog $blog)
    {

        $path = $request->route('path') ?? '';
        $query = $request->all();

        $data = DeliveryRepository::getHtml($blog, $path, $query);

        if ($data) {
            if ($data->type === DeliveryAPITypeEnum::FILE) {
                $content = base64_decode($data->content);
                return response($content, $data->status)
                    ->header('Content-Type', $data->mime_type);
            } elseif ($data->type === DeliveryAPITypeEnum::REDIRECT) {
                return redirect($data->to, $data->status);
            }
        }
    }

}
