<?php

namespace App\Http\Controllers\ConsoleAPI\Integrations;

use App\Data\Objects\ConsoleAPI\S3StorageObject;
use App\Domains\Integrations\S3\S3ConnectionDto;
use App\Domains\Integrations\S3\S3StorageService;
use App\Domains\Media\Jobs\TransferMediaToStorageJob;
use App\Models\Blog;
use App\Models\S3Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class S3StorageController
{
    public function get(Blog $blog): JsonResponse
    {
        $customS3 = S3Storage::where('blog_id', $blog->id)
            ->first();

        if (!$customS3) {
            return response()->json(null);
        }

        return response()->json(new S3StorageObject($customS3));
    }

    public function set(Request $request, Blog $blog): JsonResponse
    {
        $data = $request->validate([
            'endpoint_url' => 'required|string',
            'bucket_name' => 'required|string',
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'region' => 'nullable|string',
            'path_prefix' => 'nullable|string',
            'path_style_access' => 'required|boolean',
            'cdn_url' => 'nullable|string',
        ]);

        $conn = new S3ConnectionDto(
            endpointUrl: $data['endpoint_url'],
            bucketName: $data['bucket_name'],
            accessKey: $data['access_key'],
            secretKey: $data['secret_key'],
            pathPrefix: $data['path_prefix'],
            region: $data['region'],
            pathStyleAccess: $data['path_style_access'],
            cdnUrl: $data['cdn_url'],
        );

        $customS3 = S3Storage::where('blog_id', $blog->id)
            ->first();

        if ($customS3)
            $s3Storage = S3StorageService::updateS3Storage($customS3, $conn);
        else
            $s3Storage = S3StorageService::createS3Storage($blog, $conn);

        TransferMediaToStorageJob::dispatch($blog->id, true);

        return response()->json(new S3StorageObject($s3Storage));
    }
    
    public function testConnection(Request $request, S3StorageService $s3StorageService): JsonResponse
    {
        $data = $request->validate([
            'endpoint_url' => 'required|string',
            'bucket_name' => 'required|string',
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'region' => 'nullable|string',
            'path_prefix' => 'nullable|string',
            'path_style_access' => 'required|boolean',
            'cdn_url' => 'nullable|string',
        ]);

        $conn = new S3ConnectionDto(
            endpointUrl: $data['endpoint_url'],
            bucketName: $data['bucket_name'],
            accessKey: $data['access_key'],
            secretKey: $data['secret_key'],
            pathPrefix: $data['path_prefix'],
            region: $data['region'],
            pathStyleAccess: $data['path_style_access'],
            cdnUrl: $data['cdn_url'],
        );

        $filesystem = $s3StorageService->getFilesystem($conn);

        return response()->json($s3StorageService->test($filesystem));
    }

    public function delete(Blog $blog): JsonResponse
    {
        $customS3 = S3Storage::where('blog_id', $blog->id)
            ->first();

        if (! $customS3) {
            return response()->json(null);
        }

        TransferMediaToStorageJob::dispatch($blog->id, false);

        return response()->json(null);
    }

}
