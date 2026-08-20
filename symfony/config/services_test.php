<?php

use App\Tests\Fake\FakeHub;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Mercure\HubInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(FakeHub::class)->public();
    $services->alias(HubInterface::class, FakeHub::class)->public();
};
