<?php

namespace Rezzza\Jobflow\Extension\RabbitMq\Transport;

use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\Scheduler\TransportInterface;

class RabbitMqTransport implements TransportInterface
{
    protected $rpcClient;

    public function __construct($rpcClient)
    {
        $this->rpcClient = $rpcClient;
    }

    public function addMessage(JobMessage $msg)
    {
        $this->rpcClient->addRequest(serialize($msg), 'jobflow', $msg->getUniqName());
    }

    public function getMessage()
    {
        return $this->rpcClient->getReplies();
    }

    public function getName()
    {
        return 'rabbitmq';
    }
}