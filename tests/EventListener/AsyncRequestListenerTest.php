<?php

namespace Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Pond5\AsyncRequestBundle\EventListener\AsyncRequestListener;
use Pond5\AsyncRequestBundle\Message\AsyncRequestNotification;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \Pond5\AsyncRequestBundle\EventListener\AsyncRequestListener
 */
class AsyncRequestListenerTest extends TestCase
{
    use ProphecyTrait;

    private const HEADER = 'X-Request-Async-Test';

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $bus;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var RequestEvent|ObjectProphecy
     */
    private $event;

    /**
     * @var AsyncRequestListener
     */
    private $asyncRequestListener;

    protected function setUp(): void
    {
        $this->bus = $this->prophesize(MessageBusInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->event = $this->prophesize(RequestEvent::class);

        $this->asyncRequestListener = new AsyncRequestListener(
            $this->bus->reveal(),
            $this->logger->reveal(),
            self::HEADER
        );
    }

    /**
     * @covers ::__construct
     */
    public function testImplementsEventSubscriberInterface()
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->asyncRequestListener);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testSubscribesToKernelRequestWithLowPriority()
    {
        $subscribedEvents = $this->asyncRequestListener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribedEvents);
        $this->assertEquals(['onKernelRequest', -10], $subscribedEvents[KernelEvents::REQUEST]);
    }

    /**
     * @covers ::onKernelRequest
     * @dataProvider supportedMethodProvider
     * @param string $method
     */
    public function testOnKernelRequestForSupportedRequestMethods(string $method)
    {
        $headerBag = $this->prophesize(HeaderBag::class);
        $headerBag->get(self::HEADER)->willReturn('1');

        $request = $this->prophesize(Request::class);
        $request->getMethod()->willReturn($method);
        $request->headers = $headerBag;

        $this->event->isMasterRequest()->willReturn(true);
        $this->event->getRequest()->willReturn($request);

        $this->logger->debug('Received async request')->shouldBeCalled();
        $this->bus->dispatch(Argument::that(function ($notification) use ($request) {
            if (!($notification instanceof AsyncRequestNotification)) {
                return false;
            }
            $this->assertSame($request->reveal(), $notification->getRequest());
            return true;
        }))->willReturn(new Envelope(new \stdClass()))->shouldBeCalled();
        $this->event->setResponse(Argument::that(function ($response) {
            if (!($response instanceof Response)) {
                return false;
            }
            $this->assertSame('', $response->getContent());
            $this->assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
            $this->assertNull($response->headers->get('Content-Type'));

            return true;
        }))->shouldBeCalled();

        $this->asyncRequestListener->onKernelRequest($this->event->reveal());
    }

    /**
     * @covers ::onKernelRequest
     * @dataProvider unsupportedMethodProvider
     * @param string $method
     */
    public function testOnKernelRequestForNotSupportedRequestMethods(string $method)
    {
        $request = $this->prophesize(Request::class);
        $request->getMethod()->willReturn($method);

        $this->event->isMasterRequest()->willReturn(true);
        $this->event->getRequest()->willReturn($request);

        $this->bus->dispatch(Argument::cetera())->shouldNotBeCalled();
        $this->event->setResponse(Argument::cetera())->shouldNotBeCalled();

        $this->asyncRequestListener->onKernelRequest($this->event->reveal());
    }

    /**
     * @covers ::onKernelRequest
     */
    public function testOnKernelRequestSupportsOnlyMasterRequests()
    {
        $request = $this->prophesize(Request::class);
        $request->getMethod()->willReturn('PATCH');

        $this->event->isMasterRequest()->willReturn(false);
        $this->event->getRequest()->willReturn($request);

        $this->bus->dispatch(Argument::cetera())->shouldNotBeCalled();
        $this->event->setResponse(Argument::cetera())->shouldNotBeCalled();

        $this->asyncRequestListener->onKernelRequest($this->event->reveal());
    }

    /**
     * @covers ::onKernelRequest
     */
    public function testOnKernelRequestSupportsOnlyRequestsWithAsyncHeader()
    {
        $headerBag = $this->prophesize(HeaderBag::class);
        $headerBag->get(self::HEADER)->willReturn(null);

        $request = $this->prophesize(Request::class);
        $request->getMethod()->willReturn('PATCH');
        $request->headers = $headerBag;

        $this->event->isMasterRequest()->willReturn(true);
        $this->event->getRequest()->willReturn($request);

        $this->bus->dispatch(Argument::cetera())->shouldNotBeCalled();
        $this->event->setResponse(Argument::cetera())->shouldNotBeCalled();

        $this->asyncRequestListener->onKernelRequest($this->event->reveal());
    }

    /**
     * @return iterable
     */
    public function supportedMethodProvider(): iterable
    {
        yield ['DELETE'];
        yield ['PATCH'];
        yield ['POST'];
        yield ['PUT'];
    }

    /**
     * @return iterable
     */
    public function unsupportedMethodProvider(): iterable
    {
        yield ['GET'];
        yield ['OPTIONS'];
        yield ['HEAD'];
        yield ['SEARCH'];
    }
}
