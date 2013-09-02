<?php

require_once __DIR__.'/../vendor/autoload.php';

use Rezzza\JobFlow\Jobs;
use Rezzza\JobFlow\Io;
use Rezzza\JobFlow\Scheduler\JobFlow;
use Rezzza\JobFlow\Extension\Core\Transport\PhpTransport;

$logger = new \Monolog\Logger('jobflow');
$jobFactory = Jobs::createJobFactory();

$scheduler = $jobFactory->createJobFlow('php');
$scheduler->setLogger($logger);

$io = new Io\IoDescriptor(
    new Io\Input('file://'.__DIR__.'/fixtures.csv'),
    new Io\Output('file:///'.__DIR__.'/temp/result.json')
);

$job = $jobFactory->createBuilder('job', $io)
    ->add(
        'example_extractor', 
        'extractor',
        array(
            'class' => 'Knp\ETL\Extractor\CsvExtractor'
        )
    )
    ->add(
        'example_transformer', 
        'callback_transformer',
        array(
            'callback' => function($data, $target) {
                $target['firstname'] = $data[0];
                $target['name'] = $data[1];
                $target['url'] = sprintf('http://www.lequipe.fr/Football/FootballFicheJoueur%s.html', $data[2]);

                return json_encode($target, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        )
    )
    ->add(
        'example_loader',
        'file_loader'
    )
    ->getJob()
;

$scheduler
    ->setJob($job)
    ->init()
    ->run()
;