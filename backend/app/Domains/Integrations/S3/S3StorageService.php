<?php

namespace App\Domains\Integrations\S3;

use Aws\S3\S3Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;

class S3StorageService
{

    public function getFilesystem(S3ConnectionDto $conn): Filesystem
    {
        $client = new S3Client([
            'credentials' => [
                'key' => $conn->accessKey,
                'secret' => $conn->secretKey,
            ],
            'region' => $conn->region ?? 'us-east-1',
            'version' => 'latest',
            'endpoint' => $conn->endpointUrl,
            'use_path_style_endpoint' => $conn->pathStyleAccess,
        ]);

        $adapter = new AwsS3V3Adapter(
            $client,
            $conn->bucketName,
            $conn->pathPrefix ?? '',
            new PortableVisibilityConverter(
                Visibility::PUBLIC
            )
        );

        return new Filesystem($adapter);
    }

    /**
     * @return array<string, mixed>
     */
    public function test(Filesystem $fs): array
    {
        $filename = 'hyvor_blogs_verification_file.txt';
        $content = 'Hyvor Blogs, The Best Blogging Platform';

        $errors = [];
        $write = false;
        $read = false;
        $visibility = false;
        $publicAccess = false;
        $delete = false;
        $genericError = 'An error occurred';

        // WRITE
        try {
            $fs->write($filename, $content);
            $write = true;
        } catch (UnableToWriteFile $exception) {
            $errors['write'] = $exception->getMessage();
        } catch (FilesystemException $exception) {
            $errors['write'] = $genericError;
        }

        // READ
        try {
            $readContent = $fs->read($filename);
            if ($readContent !== $content) {
                $errors['read'] = 'Content mismatch';
            } else {
                $read = true;
            }
        } catch (UnableToReadFile $exception) {
            $errors['read'] = $exception->getMessage();
        } catch (FilesystemException $exception) {
            $errors['read'] = $genericError;
        }

        // VISIBILITY
        try {
            $fs->setVisibility($filename, Visibility::PUBLIC);
            $visibility = true;
        } catch (UnableToSetVisibility $exception) {
            $errors['visibility'] = $exception->getMessage();
        } catch (FilesystemException $exception) {
            $errors['visibility'] = $genericError;
        }

        // PUBLIC ACCESS
        $publicAccessErrorPrefix = '';
        try {
            $publicUrl = $fs->publicUrl($filename);
            $publicAccessErrorPrefix = "[$publicUrl] ";
            $response = Http::get($publicUrl);

            if (!$response->ok()) {
                $errors['public_access'] = $publicAccessErrorPrefix . 'Invalid status code: ' . $response->status();
            }

            if ($response->body() !== $content) {
                $errors['public_access'] = $publicAccessErrorPrefix . 'Content mismatch';
            }

            $publicAccess = true;
        } catch (ConnectionException $e) {
            $errors['access'] = $publicAccessErrorPrefix . 'Connection error';
        } catch (\Exception $exception) {
            $errors['access'] = $publicAccessErrorPrefix . $genericError;
        }

        // DELETE
        try {
            $fs->delete($filename);
            //$delete = true;
            $errors['delete'] = "Something went wrong";
        } catch (UnableToDeleteFile $exception) {
            $errors['delete'] = $exception->getMessage();
        } catch (FilesystemException $exception) {
            $errors['delete'] = $genericError;
        }

        return [
            'write' => $write,
            'read' => $read,
            'visibility' => $visibility,
            'public_access' => $publicAccess,
            'delete' => $delete,
            'errors' => $errors,
        ];
    }

}