<?php
namespace App\Http\Controllers\CliAPI;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogRepository;
use App\Domains\Delivery\DeliveryRepository;
use App\Domains\LocalDev\LocalDevRepository;
use App\Domains\ThemeFiles\ThemeFilesRepository;
use App\Models\LocalDev;
use Illuminate\Http\Request;

class CliAPIController {

    public function createNewLocalDeveloper()
    {
        $localDev = LocalDevRepository::createNewDev();
        return response()->json([
            'uuid' => $localDev->uuid
        ]);
    }

    public function updateFiles(Request $request, LocalDev $localDev)
    {

        $files = (array) $request->input('files');
        $reset = (bool) $request->input('reset');

        foreach ($files as $path => $content) {

            $path = trim($path, '/');
            $split = explode('/', $path);

            $file = $split[1] ?? $split[0] ?? null;
            $folder = ThemeFileFolderEnum::tryFrom(isset($split[1]) ? $split[0] : null);

            if ($file) {
                ThemeFilesRepository::createOrUpdateFile(
                    $localDev,
                    $folder,
                    $file,
                    base64_decode($content)
                );
            }

        }

        return response()->json();
    }

    public function delivery(Request $request, LocalDev $localDev)
    {
        $path = $request->input('path');
        $subdomain = $request->input('subdomain');
        $host = $request->input('host');
        $blog = BlogRepository::getBlogById(1);

        /**
         * Change URLs for navigation
         */
        if ($host) {
            $url = 'http://' . $host;

            $blog->hosting_at = 'self';
            $blog->hosting_url = $url;
            $blog->url = $url;
        }

        $response = DeliveryRepository::getResponseObject($blog, $path, $localDev);

        if (isset($response->content)) {
            $response->content = base64_encode($response->content);
        }

        return response()->json($response);
    }

}