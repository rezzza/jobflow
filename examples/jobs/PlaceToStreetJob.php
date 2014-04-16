<?php

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\JobBuilder;
use Rezzza\Jobflow\JobData;

class PlaceToStreetJob extends AbstractJobType
{
    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->add(
                'example_extractor', // name
                'json_extractor',
                [
                    'path' => 'results'
                ]
            )
            ->add(
                'example_transformer', // name
                'callback_transformer',
                [
                    'callback' => function ($value, $target) {
                        $img = sprintf(
                            'http://maps.googleapis.com/maps/api/streetview?size=800x600&location=%F,%F&fov=90&heading=235&pitch=10&sensor=false',
                            $value->geometry->location->lat,
                            $value->geometry->location->lng
                        );

                        return file_get_contents($img);
                    },
                    'metadata_write' => [
                        'place_id' => 'id' // Store $data->id in metadata in order to reuse it in loader
                    ]
                ]
            )
            ->add(
                'example_loader',
                'file_loader',
                [
                    'args' => function (Options $options) {
                        $values = $options['execution']->read();
                        $metadata = $values[0]->getMetadata();

                        return [
                            new \SplFileObject(__DIR__."/../temp/job-".$metadata['place_id'].".jpeg", 'w+')
                        ];
                    }
                ]
            )
        ;
    }

    public function setInitOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'context' => [
                'limit' => 1
            ]
        ]);
    }

    public function getName()
    {
        return 'place_to_street';
    }
}
