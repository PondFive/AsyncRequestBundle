<?php

namespace Pond5\AsyncRequestBundle\EventListener;

use Pond5\AsyncRequestBundle\Message\AsyncRequestNotification;
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
     * @var array
     */
    private array $methods;

    /**
     * @param MessageBusInterface $bus
     * @param LoggerInterface $logger
     * @param string $header
     * @param array $methods Make sure uppercased (see array_map('strtoupper', $method) in Configuration class)
     */
    public function __construct(MessageBusInterface $bus, LoggerInterface $logger, string $header, array $methods)
    {
        $this->bus = $bus;
        $this->logger = $logger;
        $this->asyncHeader = $header;
        $this->methods = $methods;
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->headers->get($this->asyncHeader) && in_array($request->getMethod(), $this->methods)) {
            $this->logger->debug('Received async request');
            $request->headers->remove($this->asyncHeader);
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
