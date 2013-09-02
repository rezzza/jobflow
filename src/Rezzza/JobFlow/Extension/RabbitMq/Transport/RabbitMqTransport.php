<?php

namespace Rezzza\JobFlow\Extension\RabbitMq\Transport;

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
        $this->rpcClient->addRequest(serialize($msg), 'job', $name);
    }

    public function getMessage()
    {
        $this->rpcClient->getReplies();
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