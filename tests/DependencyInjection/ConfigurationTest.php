<?php

namespace Tests\DependencyInjection;

use Pond5\AsyncRequestBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers \Pond5\AsyncRequestBundle\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    private const DEFAULT_HEADER = 'X-Request-Async';

    public function testGetConfigTreeBuilderReturnsTreeBuilder()
    {
        $configuration = new Configuration();
        $this->assertInstanceOf(TreeBuilder::class, $configuration->getConfigTreeBuilder());
    }

    public function testReturnedTreeBuilderHasExpectedPath()
    {
        $configuration = new Configuration();
        $this->assertSame('pond5_async_request', $configuration->getConfigTreeBuilder()->buildTree()->getPath());
    }

    public function testTransportWithDefaultHeader()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test']]);
        $this->assertSame(['transport' => 'async-request-test', 'header' => self::DEFAULT_HEADER], $config);
    }

    public function testTransportAndHeader()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test', 'header' => 'X-Header-Test']]);
        $this->assertSame(['transport' => 'async-request-test', 'header' => 'X-Header-Test'], $config);
    }

    public function testTransportIsRequired()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "transport" under "pond5_async_request" must be configured.');

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), []);
    }
}
