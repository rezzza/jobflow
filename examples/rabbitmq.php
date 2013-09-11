<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/ExampleJob.php';

use Rezzza\JobFlow\Jobs;
use Rezzza\JobFlow\Io;
use Rezzza\JobFlow\Extension\ETL\Type;
use Rezzza\JobFlow\Extension;

// Create RabbitMq Client
$rmqClient = new Extension\RabbitMq\JobRpcClient('localhost', 5672, 'guest', 'guest', '/');
$rmqClient->initClient();

// Create the JobFactory.
$builder = Jobs::createJobFactoryBuilder();
$builder->addExtension(new Extension\Core\CoreExtension());
$builder->addExtension(new Extension\ETL\ETLExtension());
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));

// Add our custom JobType. With RabbitMq calling job type with alias system is required.
// So we add it rather than  : $jobFactory->createBuilder(new ExampleJob()) 
$builder->addType(new ExampleJob());

$jobFactory = $builder->getJobFactory();

// Create the scheduler responsible for the job execution
$jobflow = $jobFactory->createJobFlow('rabbitmq');

// We can inject Logger
$jobflow->setLogger(new \Monolog\Logger('jobflow'));

// Here we go
$job = $jobFactory
    ->createBuilder('example') 
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
