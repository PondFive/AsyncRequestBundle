<?php

namespace Pond5\AsyncRequestBundle\DependencyInjection;

use Pond5\AsyncRequestBundle\EventListener\AsyncRequestListener;
use Pond5\AsyncRequestBundle\Message\AsyncRequestNotification;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class AsyncRequestExtension extends ConfigurableExtension implements PrependExtensionInterface
{
    /**
     * @inheritdoc
     */
    public function prepend(ContainerBuilder $container): void
    {
        $mergedFrameworkConfig = array_merge(...$container->getExtensionConfig('framework'));
        // check if routing for the AsyncRequestNotification has not been set manually
        if (isset($mergedFrameworkConfig['messenger']['routing'][AsyncRequestNotification::class])) {
            return;
        }

        $mergedAsyncRequestConfig = array_merge(...$container->getExtensionConfig('async_request'));
        $transport = $mergedAsyncRequestConfig['transport'];

        if (!isset($mergedFrameworkConfig['messenger']['transports'][$transport])) {
            throw new \RuntimeException(sprintf('Transport `%s` has not been set in "framework.messenger.transports".', $transport));
        }

        $config = ['messenger' => ['routing' => [AsyncRequestNotification::class => $transport]]];
        $container->prependExtensionConfig('framework', $config);
    }

    /**
     * @inheritdoc
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container
            ->getDefinition(AsyncRequestListener::class)
            ->setArgument('$header', $mergedConfig['header'])
        ;
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration();
    }
}
