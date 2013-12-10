<?php

namespace Rezzza\Jobflow\Plugin\SymfonyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container The container.
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('rezzza_jobflow.extensions')) {
            return;
        }

        $definition = $container->getDefinition('rezzza_jobflow.extensions');

        // Builds an array with service IDs as keys and tag aliases as values
        $types = array();

        foreach ($container->findTaggedServiceIds('jobflow.type') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            // Flip, because we want tag aliases (= type identifiers) as keys
            $types[$alias] = $serviceId;
        }

        $definition->replaceArgument(1, $types);

        $extensions = array();

        foreach ($container->findTaggedServiceIds('jobflow.extension') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            $extensions[$alias][] = $serviceId;
        }

        $definition->replaceArgument(2, $extensions);

        $transports = array();

        foreach ($container->findTaggedServiceIds('jobflow.transport') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            $transports[$alias] = $serviceId;
        }

        $definition->replaceArgument(3, $transports);
    }
}
