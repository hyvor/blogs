<?php

use App\Service\App\Storage\FilesystemFactory;
use App\Service\Integration\Unsplash\UnsplashService;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

return static function (ContainerConfigurator $container): void {
    $parameters = $container->parameters();
    $parameters->set('app.filesystem_default', 's3');

    $services = $container->services();
    $services->defaults()->autowire()->autoconfigure();
    $services->load('App\\', '../src/');

    $services->set(PdoSessionHandler::class)
        ->args(['%env(DATABASE_URL)%', ['db_table' => 'oidc_sessions']]);

    $services->set(S3Client::class)
        ->lazy()
        ->args(['$args' => [
            'version' => 'latest',
            'region' => '%env(default::string:S3_REGION)%',
            'endpoint' => '%env(default::string:S3_ENDPOINT)%',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => '%env(default::string:S3_ACCESS_KEY_ID)%',
                'secret' => '%env(default::string:S3_SECRET_ACCESS_KEY)%',
            ],
        ]]);

    $services->set(Filesystem::class)
        ->factory([FilesystemFactory::class, 'create'])
        ->args([
            '%env(default:app.filesystem_default:string:FILESYSTEM)%',
            new Reference(S3Client::class),
            '%env(default::string:S3_BUCKET)%',
        ]);

    $services->set(UnsplashService::class)
        ->arg('$accessKey', '%env(string:UNSPLASH_ACCESS_KEY)%');
};
