<?php

namespace Rezzza\Jobflow\Extension\RabbitMq;

use Rezzza\Jobflow\Scheduler\JobflowFactory;

class JobWorker
{
    protected $jobflowFactory;

    public function __construct(JobflowFactory $jobflowFactory)
    {
        $this->jobflowFactory = $jobflowFactory;
    }

    public function execute($msg)
    {
        $jobMsg = unserialize($msg);

        $result = $this->jobflowFactory
            ->create('rabbitmq')
            ->executeMsg($jobMsg)
        ;

        return serialize($result);
    }

    public function __invoke($msg)
    {
        return $this->execute($msg);
    }
}