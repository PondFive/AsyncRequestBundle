<?php

namespace Tests\DependencyInjection;

use Pond5\AsyncRequestBundle\DependencyInjection\Configuration;
use Pond5\AsyncRequestBundle\DependencyInjection\Pond5AsyncRequestExtension;
use PHPUnit\Framework\TestCase;
use Pond5\AsyncRequestBundle\Message\AsyncRequestNotification;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Pond5\AsyncRequestBundle\DependencyInjection\Pond5AsyncRequestExtension
 */
class Pond5AsyncRequestExtensionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @covers ::getConfiguration
     */
    public function testGetConfiguration()
    {
        $extension = new Pond5AsyncRequestExtension();
        $container = $this->prophesize(ContainerBuilder::class);
        $this->assertInstanceOf(Configuration::class, $extension->getConfiguration([], $container->reveal()));
    }

    /**
     * @covers ::loadInternal
     */
    public function testLoad()
    {
        $extension = new Pond5AsyncRequestExtension();
        $container = new ContainerBuilder();
        $config = [
            'pond5_async_request' => [
                'transport' => 'async-request-test',
                'header' => 'X-Header-Test',
                'methods' => ['get', 'TeSt'],
            ],
        ];
        $extension->load($config, $container);


        $this->assertTrue($container->hasDefinition('pond5_async_request.notification_handler'));
        $this->assertTrue($container->hasDefinition('pond5_async_request.listener'));
        $this->assertSame('X-Header-Test', $container->getDefinition('pond5_async_request.listener')->getArgument(2));
        $this->assertSame(['GET', 'TEST'], $container->getDefinition('pond5_async_request.listener')->getArgument(3));
    }

    /**
     * @covers ::prepend
     */
    public function testPrepend()
    {
        $extension = new Pond5AsyncRequestExtension();
        $container = $this->prophesize(ContainerBuilder::class);

        $container->getExtensionConfig('framework')->willReturn([['messenger' => ['transports' => ['async-request-test' => 'MESSENGER_TRANSPORT_DSN_TEST']]]]);
        $container->getExtensionConfig('pond5_async_request')->willReturn([['transport' => 'async-request-test']]);
        $container->prependExtensionConfig('framework', ['messenger' => ['routing' => [AsyncRequestNotification::class => 'async-request-test']]])->shouldBeCalled();

        $extension->prepend($container->reveal());
    }

    /**
     * @covers ::prepend
     */
    public function testPrependDoesNotPrependFrameworkExtensionWhenSetManually()
    {
        $extension = new Pond5AsyncRequestExtension();
        $container = $this->prophesize(ContainerBuilder::class);

        $container->getExtensionConfig('framework')->willReturn([['messenger' => ['routing' => [AsyncRequestNotification::class => 'transport-test']]]]);
        $container->getExtensionConfig('pond5_async_request')->shouldNotBeCalled();
        $container->prependExtensionConfig(Argument::cetera())->shouldNotBeCalled();

        $extension->prepend($container->reveal());
    }

    /**
     * @covers ::prepend
     */
    public function testPrependThrowsExceptionWhenTransportIsNotSet()
    {
        $extension = new Pond5AsyncRequestExtension();
        $container = $this->prophesize(ContainerBuilder::class);

        $container->getExtensionConfig('framework')->willReturn([]);
        $container->getExtensionConfig('pond5_async_request')->willReturn([['not-transport' => 'some-value']]);
        $container->prependExtensionConfig(Argument::cetera())->shouldNotBeCalled();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No transport provided. Setting "pond5_async_request.transport" is required.');

        $extension->prepend($container->reveal());
    }

    /**
     * @covers ::prepend
     * @testWith [""]
     *           [null]
     *           [0]
     * @param mixed $transport
     */
    public function testPrependThrowsExceptionWhenTransportIsEmpty($transport)
    {
        $extension = new Pond5AsyncRequestExtension();
        $container = $this->prophesize(ContainerBuilder::class);

        $container->getExtensionConfig('framework')->willReturn([]);
        $container->getExtensionConfig('pond5_async_request')->willReturn([['transport' => $transport]]);
        $container->prependExtensionConfig(Argument::cetera())->shouldNotBeCalled();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No transport provided. Setting "pond5_async_request.transport" is required.');

        $extension->prepend($container->reveal());
    }

    /**
     * @covers ::prepend
     */
    public function testPrependThrowsExceptionWhenTransportIsNotSetInMessengerConfig()
    {
        $extension = new Pond5AsyncRequestExtension();
        $container = $this->prophesize(ContainerBuilder::class);

        $container->getExtensionConfig('framework')->willReturn([['messenger' => ['transports' => ['async-request-test' => 'MESSENGER_TRANSPORT_DSN_TEST']]]]);
        $container->getExtensionConfig('pond5_async_request')->willReturn([['transport' => 'other-transport']]);
        $container->prependExtensionConfig(Argument::cetera())->shouldNotBeCalled();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Transport `other-transport` has not been set in "framework.messenger.transports".');

        $extension->prepend($container->reveal());
    }
}
