<?php

namespace Rezzza\JobFlow\Extension\RabbitMq;

use Thumper\RpcClient;

class JobRpcClient extends RpcClient
{
    public function processMessage($msg)
    {
        echo PHP_EOL.'Get response'.PHP_EOL;
        parent::processMessage($msg);

        $body = @unserialize($msg->body);

        if (false === $body) {
            // Error
            echo $msg->body.PHP_EOL;

            return false;
        }

        if (null === $body) {
            // Fin
            return false;
        }

        echo sprintf('Just executed : %s', $body->context->getPrevious()).PHP_EOL;

        if (null !== $body->context->getCurrent()) {
            // If job need to continue to be executed, we queue again the message
            $name = $body->context->getMessageName();

            // We need to wrap this via JobFlow. But it is quiete difficult to inject JobFlow here for the moment
            // Need to work on RabbitMqBundle to make easier RpcClient extend
            $this->addRequest(serialize($body), 'jobflow', $name.uniqid());

            echo sprintf('Request execution for : %s', $body->context->getCurrent()).PHP_EOL;
        }
    }
}