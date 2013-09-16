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


/*
        if (null !== $body->context->getCurrent()) {
            // If job need to continue to be executed, we queue again the message
            $name = $body->context->getMessageName();
            // We need to wrap this via JobFlow. But it is quiete difficult to inject JobFlow here for the moment
            // Need to work on RabbitMqBundle to make easier RpcClient extend
            $this->addRequest(serialize($body), 'jobflow', $name.uniqid());

            echo sprintf('Request execution for : %s', $body->context->getCurrent()).PHP_EOL;
        }

        if (null !== $body->input) {

        }*/
    }
}