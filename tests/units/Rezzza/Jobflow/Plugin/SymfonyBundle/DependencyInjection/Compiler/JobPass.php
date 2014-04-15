<?php

namespace Rezzza\Jobflow\Tests\Units\Plugin\SymfonyBundle\DependencyInjection\Compiler;

use mageekguy\atoum as Units;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Rezzza\Jobflow\Plugin\SymfonyBundle\DependencyInjection\Compiler\JobPass as TestedClass;

class JobPass extends Units\Test
{
    public function test_it_should_register_types()
    {
        $this
            ->if($container = new ContainerBuilder())
            ->and($pass = new TestedClass())
            ->and($diExtension = $this->getMockDiExtension())
            ->and($container->setDefinition('rezzza_jobflow.extensions', $diExtension))
            ->and($container->setDefinition('jobtype.jean.a', $this->createTypeDefinition(array('alias' => 'jean.a'))))
            ->and($container->setDefinition('jobtype.marc.a', $this->createTypeDefinition(array('alias' => 'marc.a'))))
            ->then($pass->process($container))

                ->mock($diExtension)
                    ->call('replaceArgument')
                    ->withArguments(1, array(
                        'jean.a' => 'jobtype.jean.a',
                        'marc.a' => 'jobtype.marc.a'
                    ))
                    ->once()
        ;
    }

    public function test_it_should_register_extensions()
    {
        $this
            ->if($container = new ContainerBuilder())
            ->and($pass = new TestedClass())
            ->and($diExtension = $this->getMockDiExtension())
            ->and($container->setDefinition('rezzza_jobflow.extensions', $diExtension))
            ->and($container->setDefinition('extension.jean.a', $this->createExtensionDefinition(array('alias' => 'jean.a'))))
            ->and($container->setDefinition('extension.marc.a', $this->createExtensionDefinition(array('alias' => 'marc.a'))))
            ->then($pass->process($container))

                ->mock($diExtension)
                    ->call('replaceArgument')
                    ->withArguments(2, array(
                        'jean.a' => array('extension.jean.a'),
                        'marc.a' => array('extension.marc.a')
                    ))
                    ->once()
        ;
    }

    public function test_it_should_register_transports()
    {
        $this
            ->if($container = new ContainerBuilder())
            ->and($pass = new TestedClass())
            ->and($diExtension = $this->getMockDiExtension())
            ->and($container->setDefinition('rezzza_jobflow.extensions', $diExtension))
            ->and($container->setDefinition('transport.jean.a', $this->createTransportDefinition(array('alias' => 'jean.a'))))
            ->and($container->setDefinition('transport.marc.a', $this->createTransportDefinition(array('alias' => 'marc.a'))))
            ->then($pass->process($container))

                ->mock($diExtension)
                    ->call('replaceArgument')
                    ->withArguments(3, array(
                        'jean.a' => 'transport.jean.a',
                        'marc.a' => 'transport.marc.a'
                    ))
                    ->once()
        ;
    }

    private function createTypeDefinition(array $attributes = array())
    {
        $jobtype = new \mock\Rezzza\Jobflow\JobTypeInterface;

        $definition = new Definition(get_class($jobtype));
        $definition->addTag('jobflow.type', $attributes);

        return $definition;
    }

    private function createExtensionDefinition(array $attributes = array())
    {
        $jobextension = new \mock\Rezzza\Jobflow\Extension\JobExtensionInterface;

        $definition = new Definition(get_class($jobextension));
        $definition->addTag('jobflow.extension', $attributes);

        return $definition;
    }

    private function createTransportDefinition(array $attributes = array())
    {
        $jobtransport = new \mock\Rezza\Scheduler\TransportInterface;

        $definition = new Definition(get_class($jobtransport));
        $definition->addTag('jobflow.transport', $attributes);

        return $definition;
    }

    private function getMockDiExtension()
    {
        $diExtension = new \mock\Symfony\Component\DependencyInjection\Definition();

        $diExtension->setArguments(
            array(
                'container' => null,
                'arg1' => null,
                'arg2' => null,
                'arg3' => null
            )
        );

        return $diExtension;
    }
}