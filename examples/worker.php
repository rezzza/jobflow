<?php

require_once __DIR__.'/init.php';

use Rezzza\Jobflow\Extension;

// Create RabbitMq Client
$rmqClient = new Thumper\RpcClient('localhost', 5672, 'guest', 'guest', '/');
$rmqClient->initClient();

// Add rabbitmq Extension
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

// Creates job factory
$jobflowFactory = $builder->getJobflowFactory();

// Create worker
$server = new Thumper\RpcServer('localhost', 5672, 'guest', 'guest', '/');
$server->initServer('jobflow');
$server->setCallback(new Extension\RabbitMq\JobWorker($jobflowFactory));
$server->start();
