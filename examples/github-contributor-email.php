<?php

require_once __DIR__.'/init.php';

use Rezzza\Jobflow\Extension;
use Rezzza\Jobflow\Io;

// Create RabbitMq Client
$rmqClient = new Extension\RabbitMq\JobRpcClient('localhost', 5672, 'guest', 'guest', '/');
$rmqClient->initClient();

// Add RabbitMqExtension
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));

// Create JobFactory
$jobFactory = $builder->getJobFactory();
$rmqClient->setJobFactory($jobFactory);

// Create the scheduler responsible for the job execution
$jobflow = $jobFactory->createJobflow('rabbitmq');

// We can inject Logger
$jobflow->setLogger(new \Monolog\Logger('jobflow'));

// Here we go
$job = $jobFactory
    ->createBuilder('github_email') 
    ->getJob()
;

echo 'Started...'.PHP_EOL;
// Now we can execute our job
$jobflow
    ->setJob($job)
    ->init() // Will create the first message to run the process
    ->run()
;
echo 'Ended...'.PHP_EOL;
