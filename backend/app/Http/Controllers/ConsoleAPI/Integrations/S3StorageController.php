<?php

namespace App\Http\Controllers\ConsoleAPI\Integrations;

use App\Domains\Integrations\S3\S3ConnectionDto;
use App\Domains\Integrations\S3\S3StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class S3StorageController
{

    public function set(Request $request, S3StorageService $s3StorageService): JsonResponse
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
            'test' => 'boolean',
        ]);

        $test = boolval($data['test']);

        $conn = new S3ConnectionDto(
            endpointUrl: $data['endpoint_url'],
            bucketName: $data['bucket_name'],
            accessKey: $data['access_key'],
            secretKey: $data['secret_key'],
            region: $data['region'],
            pathPrefix: $data['path_prefix'],
            pathStyleAccess: $data['path_style_access'],
            cdnUrl: $data['cdn_url'],
        );

        $filesystem = $s3StorageService->getFilesystem($conn);

        if ($test) {
            return response()->json($s3StorageService->test($filesystem));
        }

        return response()->json();
    }

}