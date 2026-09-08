<?php

use App\Service\App\Storage\FilesystemFactory;
use AsyncAws\S3\S3Client;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

return static function (ContainerConfigurator $container): void {
    $parameters = $container->parameters();

    $services = $container->services();
    $services->defaults()->autowire()->autoconfigure();
    $services->load('App\\', '../src/');

    $services->set(PdoSessionHandler::class)
        ->args(['%env(DATABASE_URL)%', ['db_table' => 'oidc_sessions']]);

    $services->set(S3Client::class)
        ->lazy()
        ->args([
            '$configuration' => [
                'endpoint' => '%env(default::string:S3_ENDPOINT)%',
                'accessKeyId' => '%env(default::string:S3_ACCESS_KEY_ID)%',
                'accessKeySecret' => '%env(default::string:S3_SECRET_ACCESS_KEY)%',
                'region' => '%env(default::string:S3_REGION)%',
                'pathStyleEndpoint' => '%env(default::bool:S3_USE_PATH_STYLE_ENDPOINT)%',
            ]
        ]);

    $services->set(Filesystem::class)
        ->factory([FilesystemFactory::class, 'create'])
        ->args([
            '%env(string:FILESYSTEM)%',
            new Reference(S3Client::class),
            '%env(default::string:S3_BUCKET)%',
        ]);
};
