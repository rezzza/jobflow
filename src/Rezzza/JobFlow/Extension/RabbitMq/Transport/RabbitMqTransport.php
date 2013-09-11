<?php

namespace Rezzza\JobFlow\Extension\RabbitMq\Transport;

use Rezzza\JobFlow\JobMessage;
use Rezzza\JobFlow\Scheduler\TransportInterface;

class RabbitMqTransport implements TransportInterface
{
    protected $rpcClient;

    public function __construct($rpcClient)
    {
        $this->rpcClient = $rpcClient;
    }

    public function addMessage(JobMessage $msg)
    {
        $name = $msg->context->getMessageName().uniqid();
        $this->rpcClient->addRequest(serialize($msg), 'jobflow', $name);
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