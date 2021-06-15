<?php

namespace Tests\MessageHandler;

use PHPUnit\Framework\TestCase;
use Pond5\AsyncRequestBundle\Message\AsyncRequestNotification;
use Pond5\AsyncRequestBundle\MessageHandler\AsyncRequestNotificationHandler;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \Pond5\AsyncRequestBundle\MessageHandler\AsyncRequestNotificationHandler
 */
class AsyncRequestNotificationHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var KernelInterface|ObjectProphecy
     */
    private $kernel;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var AsyncRequestNotification|ObjectProphecy
     */
    private $notification;

    /**
     * @var AsyncRequestNotificationHandler
     */
    private $asyncRequestNotificationHandler;

    protected function setUp(): void
    {
        $this->kernel = $this->prophesize(KernelInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->notification = $this->prophesize(AsyncRequestNotification::class);

        $this->asyncRequestNotificationHandler = new AsyncRequestNotificationHandler(
            $this->kernel->reveal(),
            $this->logger->reveal()
        );
    }

    public function testHandlerGetsSuccessResponse()
    {
        $request = $this->prophesize(Request::class);
        $this->notification->getRequest()->willReturn($request);
        $response = $this->prophesize(Response::class);
        $response->isSuccessful()->willReturn(true);
        $response->getStatusCode()->willReturn(200);
        $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST)->willReturn($response);

        $this->logger->debug('Process async request')->shouldBeCalled();
        $this->logger->info('Processed async request successfully', [
            'status_code' => 200,
        ])->shouldBeCalled();
        $this->logger->error(Argument::cetera())->shouldNotBeCalled();

        $this->asyncRequestNotificationHandler->__invoke($this->notification->reveal());
    }

    public function testHandlerGetsErrorResponse()
    {
        $request = $this->prophesize(Request::class);
        $this->notification->getRequest()->willReturn($request);
        $response = $this->prophesize(Response::class);
        $response->isSuccessful()->willReturn(false);
        $response->getStatusCode()->willReturn(404);
        $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST)->willReturn($response);

        $this->logger->debug('Process async request')->shouldBeCalled();
        $this->logger->error('Processing async request failed', [
            'status_code' => 404,
        ])->shouldBeCalled();
        $this->logger->info(Argument::cetera())->shouldNotBeCalled();

        $this->asyncRequestNotificationHandler->__invoke($this->notification->reveal());
    }
}
