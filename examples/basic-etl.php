<?php

require_once __DIR__.'/../vendor/autoload.php';

use Rezzza\Jobflow\Extension;
use Rezzza\Jobflow\Jobs;
use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\Extension\ETL\Type;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

$builder = Jobs::createJobsBuilder();
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

// If you don't need to inject extensions as above you can create directly factories :
// Jobs::createJobFactory()
// Jobs::createJobflowFactory()
$jobFactory = $builder->getJobFactory();
$jobflowFactory = $builder->getJobflowFactory();

// We will inject IO to our job to indicate where Extractor needs to read and where Loader needs to write
$io = new Io\IoDescriptor(
    new Io\Input(new Io\Driver\File('file://'.__DIR__.'/fixtures.csv')),
    new Io\Output(new Io\Driver\File('file:///'.__DIR__.'/temp/result.json'))
);

// Here we go, you can build job on the fly
$job = $jobFactory
    ->createBuilder('job') // 'job' is the JobType by default.
    ->add(
        'example_extractor', // name
        new Type\Extractor\CsvExtractorType() // or you can use 'csv_extractor' as ETLExtension is loaded by default
    )
    ->add(
        'example_transformer', // name
        new Type\Transformer\CallbackTransformerType(), // or 'callback_transformer'
        array(
            'callback' => function($data, $context) {
                $target = array(
                    'firstname' => $data[0],
                    'name' => $data[1],
                    'url' => sprintf('http://www.lequipe.fr/Football/FootballFicheJoueur%s.html', $data[2]),
                );

                return json_encode($target, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        )
    )
    ->add(
        'example_loader', // name
        new Type\Loader\FileLoaderType() // or 'file_loader'
    )
    ->getJob() // builder create the Job with this method
;

$jobflowFactory
    ->create('php')
    ->run(new ExecutionContext($job), [], $io)
;
