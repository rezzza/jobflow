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
            'processor' => function($input, $output, $execution) {
                $nums = range(0, 9);
                $execution->setGlobalOption('total', count($nums));

                foreach ($nums as $num) {
                    $output->write($num * 2);
                }
            }
        )
    )
    ->add(
        'addition',
        'job',
        array(
            'processor' => function($input, $output, $context) {
                foreach ($input->read() as $key => $data) {
                    $output->write($data + 1);
                }
            }
        )
    )
    ->getJob()
;

// Now we can execute our job
$jobflowFactory
    ->create('php')
    ->execute($job)
;
