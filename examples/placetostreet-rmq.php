<?php

require_once __DIR__.'/init.php';

use Rezzza\Jobflow\Extension;
use Rezzza\Jobflow\Io;

// Create RabbitMq Client
$rmqClient = new Extension\RabbitMq\JobRpcClient('localhost', 5672, 'guest', 'guest', '/');
$rmqClient->initClient();

// Add RabbitMqExtension
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));

// Add Monolog extension
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

// Create JobFactory
$jobFactory = $builder->getJobFactory();
$rmqClient->setJobFactory($jobFactory);

// Create the scheduler responsible for the job execution
$jobflow = $jobFactory->createJobflow('rabbitmq');

// Warning io is set in PlaceToStreetJob as we need also the same in worker.php
// Moreover : Don't forget to insert your google api key

// Here we go, gets the job
$job = $jobFactory
    ->createBuilder('place_to_street') 
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

// Then you have to launch the worker.php