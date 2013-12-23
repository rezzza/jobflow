<?php

require_once __DIR__.'/init.php';

use Rezzza\Jobflow\Jobs;
use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\Extension\ETL\Type;
use Rezzza\Jobflow\Extension;

$builder = Jobs::createJobsBuilder();
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

// If you don't need to inject extensions as above you can create directly factories :
// Jobs::createJobFactory()
// Jobs::createJobflowFactory()
$jobFactory = $builder->getJobFactory();
$jobflowFactory = $builder->getJobflowFactory();

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
            'callback' => function($data, $target) {
                $value = $data->getValue();
                $target['firstname'] = $value[0];
                $target['name'] = $value[1];
                $target['url'] = sprintf('http://www.lequipe.fr/Football/FootballFicheJoueur%s.html', $value[2]);

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

// Now we can run execution
$jobflowFactory
    ->create('php')
    ->run(
        $job,
        [],
        new Io\IoDescriptor(
            new Io\InputAggregator([
                new Io\Input('file://'.__DIR__.'/fixtures-om.csv'),
                new Io\Input('file://'.__DIR__.'/fixtures.csv'),
            ]),
            new Io\Output('file:///'.__DIR__.'/temp/result.json')
        )
    )
;
