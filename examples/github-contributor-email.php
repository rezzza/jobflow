<?php

require_once __DIR__.'/init.php';

use Rezzza\Jobflow\Extension;

// Create RabbitMq Client
$rmqClient = new Extension\RabbitMq\JobRpcClient('localhost', 5672, 'guest', 'guest', '/');
$rmqClient->initClient();

// Add RabbitMqExtension
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));

// Add monolog extension
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

// Create JobFactory
$jobflowFactory = $builder->getJobflowFactory();

$rmqClient->setJobflowFactory($jobflowFactory);

// Create the scheduler responsible for the job execution
$jobflow = $jobflowFactory->create('rabbitmq');

echo 'Started...'.PHP_EOL;
// Now we can execute our job
$jobflow
    ->setJob('github_email')
    ->init() // Will create the first message to run the process
    ->run()
;
echo 'Ended...'.PHP_EOL;
