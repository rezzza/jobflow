<?php

namespace Rezzza\Jobflow\Extension\RabbitMq;

use Thumper\RpcClient;

use Rezzza\Jobflow\JobFactory;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\Scheduler\JobflowFactory;

class JobRpcClient extends RpcClient
{
    private $jobflowFactory;

    public function setJobflowFactory(JobflowFactory $jobflowFactory)
    {
        $this->jobflowFactory = $jobflowFactory;

        return $this;
    }

    public function processMessage($msg)
    {
        parent::processMessage($msg);

        // We add @ because php throws a notice if cannot unserialize.
        $jobMsg = @unserialize($msg->body);

        if (false === $jobMsg) {
            // Display error and stop
            throw new \RuntimeException($msg->body);
        }
        
        return $this->jobflowFactory
            ->create('rabbitmq')
            ->handleMessage($jobMsg)
        ;
    }
}