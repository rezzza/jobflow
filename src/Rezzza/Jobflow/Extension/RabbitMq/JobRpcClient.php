<?php

namespace Rezzza\Jobflow\Extension\RabbitMq;

use Thumper\RpcClient;

use Rezzza\Jobflow\JobMessage;

class JobRpcClient extends RpcClient
{
    private $jobFactory;

    public function setJobFactory($jobFactory)
    {
        $this->jobFactory = $jobFactory;
    }

    public function processMessage($msg)
    {
        echo PHP_EOL.'Get response'.PHP_EOL;

        parent::processMessage($msg);

        // We add @ because php throws a notice if cannot unserialize.
        $jobMsg = @unserialize($msg->body);

        if (false === $jobMsg) {
            // Display error and stop
            echo $msg->body.PHP_EOL;

            return false;
        }

        if (!$jobMsg instanceof JobMessage) {
            // End
            echo 'no more response'.PHP_EOL;

            return false;
        }

        echo sprintf('Just executed : %s', $jobMsg->context->getCurrent()).PHP_EOL;
        
        $job = $this->jobFactory->create($jobMsg->context->getJobId());

        $scheduler = $this->jobFactory
            ->createJobflow('rabbitmq')
            ->setJob($job)
            ->handleMessage($jobMsg)
        ;
    }
}