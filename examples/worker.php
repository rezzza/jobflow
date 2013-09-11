<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/ExampleJob.php';

use Rezzza\JobFlow\Jobs;
use Rezzza\JobFlow\Extension;

// Create RabbitMq Client
$rmqClient = new Thumper\RpcClient('localhost', 5672, 'guest', 'guest', '/');
$rmqClient->initClient();

// Create the JobFactory.
$builder = Jobs::createJobFactoryBuilder();
$builder->addExtension(new Extension\Core\CoreExtension());
$builder->addExtension(new Extension\ETL\ETLExtension());
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));
$builder->addType(new ExampleJob());

$jobFactory = $builder->getJobFactory();

$server = new Thumper\RpcServer('localhost', 5672, 'guest', 'guest', '/');
$server->initServer('jobflow');
$server->setCallback(new Extension\RabbitMq\JobWorker($jobFactory));
$server->start();

