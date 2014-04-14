<?php

namespace Rezzza\Jobflow\Tests\Units\Plugin\SymfonyBundle\DependencyInjection;

use mageekguy\atoum as Units;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Rezzza\Jobflow\Plugin\SymfonyBundle\DependencyInjection\RezzzaJobflowExtension as TestedClass;

class RezzzaJobflowExtension extends Units\Test
{
    public function test_it_load_config_rabbit()
    {
        $this
            ->if($container = $this->getMockContainer())
            ->and($extension = new \mock\Rezzza\Jobflow\Plugin\SymfonyBundle\DependencyInjection\RezzzaJobflowExtension)
            ->and($extension->getMockController()->isRabbitMqLoaded = true)
            ->and($container->registerExtension($extension))
            ->then($container = $this->loadConfigFile($container, 'rabbit.yml'))
                ->boolean($container->has('rezzza_jobflow.rabbitmq.rpc_client'))
                    ->isTrue()

                ->boolean($container->has('rezzza_jobflow.rabbitmq.rpc_server'))
                    ->isTrue()
        ;
    }

    public function test_it_does_not_load_config_rabbit()
    {
        $this
            ->if($container = $this->getMockContainer())
            ->and($extension = new \mock\Rezzza\Jobflow\Plugin\SymfonyBundle\DependencyInjection\RezzzaJobflowExtension)
            ->and($extension->getMockController()->isRabbitMqLoaded = false)
            ->and($container->registerExtension($extension))
            ->then($container = $this->loadConfigFile($container, 'rabbit.yml'))
                ->boolean($container->has('rezzza_jobflow.rabbitmq.rpc_client'))
                    ->isFalse()

                ->boolean($container->has('rezzza_jobflow.rabbitmq.rpc_server'))
                    ->isFalse()
        ;
    }

    private function getMockContainer($debug = false)
    {
        return new \mock\Symfony\Component\DependencyInjection\ContainerBuilder(new ParameterBag(array('kernel.debug' => $debug)));
    }

    private function loadConfigFile($container, $file)
    {
        $locator = new FileLocator(__DIR__.'/Fixtures');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load($file);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}