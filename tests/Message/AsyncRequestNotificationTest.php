<?php

namespace Tests\Message;

use PHPUnit\Framework\TestCase;
use Pond5\AsyncRequestBundle\Message\AsyncRequestNotification;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Pond5\AsyncRequestBundle\Message\AsyncRequestNotification
 */
class AsyncRequestNotificationTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var Request|ObjectProphecy
     */
    private $request;

    /**
     * @var AsyncRequestNotification
     */
    private $asyncRequestNotification;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(Request::class);
        $this->asyncRequestNotification = new AsyncRequestNotification($this->request->reveal());
    }

    public function testSetGetRequest()
    {
        $this->assertSame($this->request->reveal(), $this->asyncRequestNotification->getRequest());
    }

    public function testSerialize()
    {
        $duplicatedRequest = $this->prophesize(Request::class);
        $this->request->duplicate(null, null, [])->willReturn($duplicatedRequest->reveal());
        $this->request->getContent()->shouldBeCalled();

        $this->assertSame(['request' => $duplicatedRequest->reveal()], $this->asyncRequestNotification->__serialize());
    }
}
