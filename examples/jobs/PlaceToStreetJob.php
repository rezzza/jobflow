<?php

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\DelayedArg;
use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\JobBuilder;

class PlaceToStreetJob extends AbstractJobType
{
    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->add(
                'example_extractor', // name
                'json_extractor',
                array(
                    'path' => 'results.*.geometry',
                    'io' => new Io\IoDescriptor(
                       new Io\Input('https://maps.googleapis.com/maps/api/place/textsearch/json?query=pub+in+marseille+france&sensor=false&key=AIzaSyCuR9yU9lRmzdnyU7YWVKZZRUIsymWkQdU')
                    )
                )
            )
            ->add(
                'example_transformer', // name
                'callback_transformer',
                array(
                    'callback' => function($data, $target) {
                        var_dump($data->location); exit;
                        $img = sprintf(
                            'http://maps.googleapis.com/maps/api/streetview?size=800x600&location=%F,%F&fov=90&heading=235&pitch=10&sensor=false', 
                            $data->location->lat,
                            $data->location->lng
                        );

                        return file_get_contents($img);
                    }
                )
            )
            ->add(
                'example_loader',
                'file_loader',
                array(
                    'args' => function(Options $options) {
                        return array(new \SplFileObject(__DIR__."/../temp/job-".uniqid().".jpeg", 'w+'));
                    }
                )
            )
        ;
    }

    public function getContextOptions()
    {
        return array(
            'limit' => 1
        );
    }

    public function getName()
    {
        return 'place_to_street';
    }
}