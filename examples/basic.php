<?php

require_once __DIR__.'/../vendor/autoload.php';

use Rezzza\Jobflow\Jobs;
use Rezzza\Jobflow\Extension;

$builder = Jobs::createJobsBuilder();
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

$jobflowFactory = $builder->getJobflowFactory();
$jobFactory = Jobs::createJobFactory();

$job = $jobFactory
    ->createBuilder('job')
    ->add(
        'multiplicator',
        'job',
        array(
            'processor' => function($execution) {
                $nums = range(0, 9);
                $execution->setContextOption('total', count($nums));

                foreach ($nums as $num) {
                    $execution->write($num * 2);
                }
            }
        )
    )
    ->add(
        'addition',
        'job',
        array(
            'processor' => function($execution) {
                foreach ($execution->read() as $key => $data) {
                    $execution->write($data->getValue() + 1);
                }
            }
        )
    )
    ->getJob()
;

// Now we can execute our job
$jobflowFactory
    ->create('php')
    ->run($job)
;
