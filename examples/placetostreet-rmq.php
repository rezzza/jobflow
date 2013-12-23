<?php

require_once __DIR__.'/init.php';

use Rezzza\Jobflow\Extension;
use Rezzza\Jobflow\Io;

// Create RabbitMq Client
$rmqClient = new Extension\RabbitMq\JobRpcClient(
    new \PhpAmqpLib\Connection\AMQPConnection('localhost', 5672, 'guest', 'guest', '/')
);
$rmqClient->initClient();

// Add RabbitMqExtension
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));

// Add Monolog extension
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

// Create JobFactory
$jobflowFactory = $builder->getJobflowFactory();
$rmqClient->setJobflowFactory($jobflowFactory);

// Moreover : Don't forget to insert your google api key
echo 'Started...'.PHP_EOL;
// Now we can execute our job
$jobflowFactory
    ->create('rabbitmq')
    ->run(
        'place_to_street',
        array(),
        new Io\IoDescriptor(
            new Io\Input('https://maps.googleapis.com/maps/api/place/textsearch/json?query=pub+in+marseille+france&sensor=false&key=AIzaSyCuR9yU9lRmzdnyU7YWVKZZRUIsymWkQdU')
        )
    )
;
echo 'Ended...'.PHP_EOL;

// Then you have to launch the worker.php