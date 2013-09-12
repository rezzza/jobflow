<?php

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;
use Rezzza\JobFlow\Io;
use Rezzza\JobFlow\JobBuilder;

class GithubEmailJob extends AbstractJobType
{
    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->add(
                'extract_user_url',
                'json_extractor',
                array(
                    'path' => '*.url'
                )
            )
            ->add(
                'get_user_url',
                'callback_transformer',
                array(
                    'callback' => function($data, $target) {
                        $target['url'] = $data.'?access_token=YOURAPIKEY';

                        return $target;
                    }
                )
            )
            ->add(
                'users_loader',
                'pipe_loader',
                array(
                    'mapping' => array(
                        'url' => 'dsn'
                    )
                )
            )
            ->add(
                'email_extractor', 
                'json_extractor'
            )
            ->add(
                'get_user_email',
                'callback_transformer',
                array(
                    'callback' => function($data, $target) {
                        if (property_exists($data, 'email') && strlen($data->email)) {
                            return $data->email."\n";
                        }

                        return '';
                    }
                )
            )
            ->add(
                'email_loader',
                'file_loader'
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'io' => new Io\IoDescriptor(
                new Io\Input('https://api.github.com/repos/symfony/symfony/contributors?access_token=YOURAPIKEY'),
                new Io\Output('file:///'.__DIR__."/../temp/email.csv")
            ),
            'context' => array(
                'limit' => 10
            )
        ));
    }

    public function getName()
    {
        return 'github_email';
    }
}