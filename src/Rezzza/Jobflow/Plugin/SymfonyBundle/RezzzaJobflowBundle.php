<?php

namespace Rezzza\Jobflow\Plugin\SymfonyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Rezzza\Jobflow\Plugin\SymfonyBundle\DependencyInjection\Compiler\JobPass;

class RezzzaJobflowBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new JobPass());
    }
}
