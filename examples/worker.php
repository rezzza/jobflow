<?php

require_once __DIR__.'/init.php';

use Rezzza\JobFlow\Jobs;
use Rezzza\JobFlow\Extension;

// Create RabbitMq Client
$rmqClient = new Thumper\RpcClient('localhost', 5672, 'guest', 'guest', '/');
$rmqClient->initClient();

// Add rabbitmq Extension
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));

// Creates job factory
$jobFactory = $builder->getJobFactory();

// Create worker
$server = new Thumper\RpcServer('localhost', 5672, 'guest', 'guest', '/');
$server->initServer('jobflow');
$server->setCallback(new Extension\RabbitMq\JobWorker($jobFactory));
$server->start();

