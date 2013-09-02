<?php

namespace Rezzza\JobFlow\Extension\RabbitMq;

use Rezzza\JobFlow\Extension\BaseExtension;

class RabbitMqExtension extends BaseExtension
{
    private $rpcClient;

    public function __construct($rpcClient)
    {
        $this->rpcClient = $rpcClient;
    }

    public function loadTransports()
    {
        return array(
            new Transport\RabbitMqTransport($this->rpcClient)
        );
    }
}