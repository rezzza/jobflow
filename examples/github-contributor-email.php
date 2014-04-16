<?php

require_once __DIR__.'/init.php';

use Rezzza\Jobflow\Extension;
use Rezzza\Jobflow\Io;

// Create RabbitMq Client
$amqpConnection = new \PhpAmqpLib\Connection\AMQPConnection('localhost', 5672, 'guest', 'guest', '/');
$amqpConnection->set_close_on_destruct(false);
$rmqClient = new Extension\RabbitMq\JobRpcClient($amqpConnection);
$rmqClient->initClient();

// Add RabbitMqExtension
$builder->addExtension(new Extension\RabbitMq\RabbitMqExtension($rmqClient));

// Add monolog extension
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

// Create JobFactory
$jobflowFactory = $builder->getJobflowFactory();

$rmqClient->setJobflowFactory($jobflowFactory);

echo 'Started...'.PHP_EOL;
// Now we can execute our job
$jobflowFactory
    ->create('rabbitmq')
    ->run(
        'github_email',
        array(),
        new Io\IoDescriptor(
            new Io\Input(new Io\Driver\File('https://api.github.com/repos/symfony/console/stargazers?access_token=236b93940ce523226035931f67d2de6bcc1aeab9')),
            new Io\Output(new Io\Driver\File('file://'.__DIR__."/temp/email.csv"))
        )
    )
;
echo 'Ended...'.PHP_EOL;
