<?php

namespace Rezzza\Jobflow\Plugin\SymfonyBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Rezzza\Jobflow\Scheduler\JobflowFactory;

abstract class AbstractJobCommand extends Command
{
    protected $jobOptions = array();

    protected $jobId;

    protected $transport;

    protected $jobflow;

    public function __construct(JobflowFactory $jobflow)
    {
        $this->jobflow = $jobflow;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->jobflow
            ->create($this->transport)
            ->run($this->jobId, $this->jobOptions)
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
