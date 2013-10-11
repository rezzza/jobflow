<?php

use Symfony\Component\OptionsResolver\Options;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\JobBuilder;
use Rezzza\Jobflow\Processor\Call\MetadataCall;

class PlaceToStreetJob extends AbstractJobType
{
    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->add(
                'example_extractor', // name
                'json_extractor',
                array(
                    'path' => 'results',
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
                        $img = sprintf(
                            'http://maps.googleapis.com/maps/api/streetview?size=800x600&location=%F,%F&fov=90&heading=235&pitch=10&sensor=false', 
                            $data->geometry->location->lat,
                            $data->geometry->location->lng
                        );

                        return file_get_contents($img);
                    },
                    'metadata_write' => array(
                        'id' => 'place_id' // Store $data->id in metadata in order to reuse it in loader
                    )
                )
            )
            ->add(
                'example_loader',
                'file_loader',
                array(
                    'args' => function(Options $options) {
                        $id = $options['message']->getMetadata('place_id');

                        return array(new \SplFileObject(__DIR__."/../temp/job-".$id.".jpeg", 'w+'));
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