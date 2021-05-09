<?php

namespace jlekowski\AsyncRequestBundle\EventListener;

use jlekowski\AsyncRequestBundle\Message\AsyncRequestNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class AsyncRequestListener implements EventSubscriberInterface
{
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $bus;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var string
     */
    private string $asyncHeader;

    /**
     * @param MessageBusInterface $bus
     * @param LoggerInterface $logger
     * @param string $header
     */
    public function __construct(MessageBusInterface $bus, LoggerInterface $logger, string $header)
    {
        $this->bus = $bus;
        $this->logger = $logger;
        $this->asyncHeader = $header;
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (
            $event->isMasterRequest()
            && in_array($request->getMethod(), ['DELETE', 'PATCH', 'POST', 'PUT'])
            && $request->headers->get($this->asyncHeader)
        ) {
            $this->logger->debug('Received async request');
            $this->bus->dispatch(new AsyncRequestNotification($request));
            $event->setResponse(new Response(null, Response::HTTP_ACCEPTED, ['Content-Type' => null]));
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -10] // run after all other events
        ];
    }
}
