<?php

namespace Rezzza\Jobflow\Plugin\SymfonyBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thumper\RpcServer;

class WorkerCommand extends Command
{
    private $rpcServer;

    public function __construct(RpcServer $rpcServer)
    {
        $this->rpcServer = $rpcServer;
        parent::__construct('jobflow:worker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rpcServer->start();
    }
}
