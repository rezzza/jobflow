<?php

namespace Rezzza\Jobflow\Plugin\SymfonyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('jobflow:worker')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $container
            ->get('rezzza_jobflow.rabbitmq.rpc_server')
            ->start()
        ;
    }
}