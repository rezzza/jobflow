<?php

namespace Rezzza\Jobflow\Extension\RabbitMq;

use Rezzza\Jobflow\Extension\BaseExtension;

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