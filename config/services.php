<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Pond5\AsyncRequestBundle\EventListener\AsyncRequestListener;
use Pond5\AsyncRequestBundle\MessageHandler\AsyncRequestNotificationHandler;

return static function (ContainerConfigurator $configurator) {
    $configurator->services()

        ->set('pond5_async_request.listener', AsyncRequestListener::class)
            ->args([
                service('messenger.bus.default'),
                service('logger')
            ])
            ->tag('kernel.event_subscriber')
            ->tag('monolog.logger', ['channel' => 'async_request'])

        ->set('pond5_async_request.notification_handler', AsyncRequestNotificationHandler::class)
            ->args([
                service('kernel'),
                service('logger')
            ])
            ->tag('messenger.message_handler')
            ->tag('monolog.logger', ['channel' => 'async_request'])
    ;
};
