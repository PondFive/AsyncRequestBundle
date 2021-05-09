<?php

namespace jlekowski\AsyncRequestBundle\MessageHandler;

use jlekowski\AsyncRequestBundle\Message\AsyncRequestNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AsyncRequestNotificationHandler implements MessageHandlerInterface
{
    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param KernelInterface $kernel
     * @param LoggerInterface $logger
     */
    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    /**
     * @param AsyncRequestNotification $notification
     * @throws \Exception
     */
    public function __invoke(AsyncRequestNotification $notification): void
    {
        $request = $notification->getRequest();
        $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST);

        if ($response->isSuccessful()) {
            $this->logger->info('Processed async request successfully', [
                'status_code' => $response->getStatusCode(),
            ]);
        } else {
            $this->logger->error('Processing async request failed', [
                'status_code' => $response->getStatusCode(),
            ]);
        }
    }
}
