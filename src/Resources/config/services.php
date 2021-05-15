<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Pond5\AsyncRequestBundle\EventListener\AsyncRequestListener;
use Pond5\AsyncRequestBundle\MessageHandler\AsyncRequestNotificationHandler;

return static function (ContainerConfigurator $configurator) {
    // default configuration for services in *this* file
    $services = $configurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;

    $services->set(AsyncRequestListener::class);
    $services->set(AsyncRequestNotificationHandler::class);
};
