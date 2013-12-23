<?php

require_once __DIR__.'/init.php';

use Rezzza\Jobflow\Extension;

$amqpConnection = new \PhpAmqpLib\Connection\AMQPConnection('localhost', 5672, 'guest', 'guest', '/');

// Create RabbitMq Client
$rmqClient = new Thumper\RpcClient($amqpConnection);
$rmqClient->initClient();

// Add rabbitmq Extension
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

// Creates job factory
$jobflowFactory = $builder->getJobflowFactory();

// Create worker
$server = new Thumper\RpcServer($amqpConnection);
$server->initServer('jobflow');
$server->setCallback(new Extension\RabbitMq\JobWorker($jobflowFactory));
$server->start();
