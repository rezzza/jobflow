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
                    'path' => 'results.*.geometry'
                )
            )
            ->add(
                'example_transformer', // name
                'callback_transformer',
                array(
                    'callback' => function($data, $target) {
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
                    'etl_config' => function(Options $options) {
                        $class = $options['class'];
                        $file = function() {
                            return new \SplFileObject(__DIR__."/../temp/job-".uniqid().".jpeg", 'w+');
                        };

                        return array(
                            'class' => $class,
                            'args' => array(new DelayedArg($file))
                        );
                    } 
                )
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'io' => new Io\IoDescriptor(
                new Io\Input('https://maps.googleapis.com/maps/api/place/textsearch/json?query=pub+in+marseille+france&sensor=false&key=AIzaSyCgYbLbVd7g2dfjDLJCBGjcZJvpqT-4Dsc')
            ),
            'context' => array(
                'limit' => 1
            )
        ));
    }

    public function getName()
    {
        return 'place_to_street';
    }
}