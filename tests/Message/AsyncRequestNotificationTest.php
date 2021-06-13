<?php

namespace Tests\Message;

use PHPUnit\Framework\TestCase;
use Pond5\AsyncRequestBundle\Message\AsyncRequestNotification;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Pond5\AsyncRequestBundle\Message\AsyncRequestNotification
 */
class AsyncRequestNotificationTest extends TestCase
{
    use ProphecyTrait;

    public function testSetGetRequest()
    {
        $request = $this->prophesize(Request::class);
        $asyncRequestNotification = new AsyncRequestNotification($request->reveal());

        $this->assertSame($request->reveal(), $asyncRequestNotification->getRequest());
    }
}
