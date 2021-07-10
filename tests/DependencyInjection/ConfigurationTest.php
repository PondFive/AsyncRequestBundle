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
    private const DEFAULT_METHODS = ['DELETE', 'PATCH', 'POST', 'PUT'];

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

    public function testTransportWithOtherDefault()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test']]);
        $this->assertEquals(['transport' => 'async-request-test', 'header' => self::DEFAULT_HEADER, 'methods' => self::DEFAULT_METHODS], $config);
    }

    public function testHeader()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test', 'header' => 'X-Header-Test']]);
        $this->assertEquals(['transport' => 'async-request-test', 'header' => 'X-Header-Test', 'methods' => self::DEFAULT_METHODS], $config);
    }

    public function testMethods()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test', 'methods' => ['POST', 'PATCH']]]);
        $this->assertEquals(['transport' => 'async-request-test', 'header' => self::DEFAULT_HEADER, 'methods' => ['POST', 'PATCH']], $config);
    }

    public function testMethodsAreUppercased()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test', 'methods' => ['get', 'tEsT']]]);
        $this->assertEquals(['transport' => 'async-request-test', 'header' => self::DEFAULT_HEADER, 'methods' => ['GET', 'TEST']], $config);
    }

    public function testMethodsCannotBeEmpty()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The path "pond5_async_request.methods" should have at least 1 element(s) defined.');

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test', 'methods' => []]]);
    }

    public function testMethodsCannotBeNull()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The path "pond5_async_request.methods" should have at least 1 element(s) defined.');

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test', 'methods' => null]]);
    }

    public function testAll()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['transport' => 'async-request-test', 'header' => 'X-Header-Test', 'methods' => ['post']]]);
        $this->assertEquals(['transport' => 'async-request-test', 'header' => 'X-Header-Test', 'methods' => ['POST']], $config);
    }

    public function testTransportIsRequired()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "transport" under "pond5_async_request" must be configured');

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), []);
    }
}
