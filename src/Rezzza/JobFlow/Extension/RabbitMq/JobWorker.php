<?php

namespace Rezzza\JobFlow\Extension\RabbitMq;

class JobWorker
{
    protected $jobFactory;

    public function __construct($jobFactory)
    {
        $this->jobFactory = $jobFactory;
    }

    public function execute($msg)
    {
        $jobMsg = unserialize($msg);

        $job = $this->jobFactory->create($jobMsg->context->getJobId());

        $scheduler = $this->jobFactory->createJobFlow('rabbitmq');

        $result = $scheduler
            ->setJob($job)
            ->run($jobMsg)
        ;

        return serialize($result);
    }

    public function __invoke($msg)
    {
        return $this->execute($msg);
    }
}