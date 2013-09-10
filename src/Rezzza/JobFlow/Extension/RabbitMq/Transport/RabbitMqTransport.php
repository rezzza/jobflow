<?php

namespace Rezzza\JobFlow\Extension\RabbitMq\Transport;

use Rezzza\JobFlow\Scheduler\TransportInterface;

class RabbitMqTransport implements TransportInterface
{
    protected $rpcClient;

    public $result = null;

    public function __construct($rpcClient)
    {
        $this->rpcClient = $rpcClient;
    }

    public function addMessage($msg, $name = null)
    {
        $this->rpcClient->addRequest(serialize($msg), 'jobflow', $name.uniqid());
    }

    public function getMessage()
    {
        return $this->rpcClient->getReplies();
    }

    public function store($result)
    {
        return $result;
    }

    public function getName()
    {
        return 'rabbitmq';
    }
}