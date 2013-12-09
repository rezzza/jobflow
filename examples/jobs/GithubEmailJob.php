<?php

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\JobBuilder;

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
                        $target['url'] = $data.'?access_token=236b93940ce523226035931f67d2de6bcc1aeab9';

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

    public function setInitOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'context' => array(
                'limit' => 15
            )
        ));
    }

    public function getName()
    {
        return 'github_email';
    }
}