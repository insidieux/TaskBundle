<?php
namespace TaskBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TaskExtension
 * @package TaskBundle\DependencyInjection
 */
class TaskExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['namespaces'])) {
            foreach ($config['namespaces'] as $namespace) {
                $container->setDefinition(sprintf('task.worker.%s', $namespace), $this->makeWorker($namespace));
            }
        }

        if (!$container->hasDefinition('task.worker.common')) {
            $container->setDefinition('task.worker.common', $this->makeWorker('common'));
        }
    }

    /**
     * @param string $namespace
     * @return Definition
     */
    private function makeWorker(string $namespace)
    {
        $definition = new Definition();
        $definition->setClass('TaskBundle\Services\Worker');
        $definition->addArgument(new Reference('task.services.locker'));
        $definition->addArgument($namespace);
        $definition->setPublic(true);
        $definition->setAbstract(false);
        $definition->addTag('console.command');
        return $definition;
    }
}
