<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/jobs/PlaceToStreetJob.php';
require_once __DIR__.'/jobs/GithubEmailJob.php';

use Rezzza\Jobflow\Jobs;
use Rezzza\Jobflow\Extension;

// Create the JobFactory.
$builder = Jobs::createJobsBuilder();

// Add our custom JobType. With RabbitMq calling job type with alias system is required.
// So we add it rather than  : $jobFactory->createBuilder(new ExampleJob()) 
$builder->addType(new PlaceToStreetJob());
$builder->addType(new GithubEmailJob());