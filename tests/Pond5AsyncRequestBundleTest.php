<?php

namespace Tests;

use Pond5\AsyncRequestBundle\Pond5AsyncRequestBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @covers \Pond5\AsyncRequestBundle\Pond5AsyncRequestBundle
 */
class Pond5AsyncRequestBundleTest extends TestCase
{
    public function testExtendsSymfonyBundleClass()
    {
        $this->assertInstanceOf(Bundle::class, new Pond5AsyncRequestBundle());
    }
}
