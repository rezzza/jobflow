<?php

namespace Rezzza\Jobflow\Plugin\SymfonyBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JobCommand extends AbstractJobCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('jobflow:run')
            ->addOptionTransport()
            ->addArgumentId()
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->transport = $input->getOption('transport');
        $this->jobId = $input->getArgument('id');

        parent::execute($input, $output);
    }
}