<?php

namespace Rezzza\Jobflow\Plugin\SymfonyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractJobCommand extends Command
{
    protected $jobOptions = array();

    protected $jobId;

    protected $transport;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->getContainer()
            ->get('rezzza_jobflow.flow')
            ->create($this->transport)
            ->execute($this->jobId, $this->jobOptions)
        ;
    }

    protected function addOptionTransport()
    {
        return $this->addOption('transport', 't', InputOption::VALUE_REQUIRED, 'Which transport used', 'php');
    }

    protected function addArgumentId()
    {
        return $this->addArgument('id', InputArgument::REQUIRED, 'Job service id');
    }
}