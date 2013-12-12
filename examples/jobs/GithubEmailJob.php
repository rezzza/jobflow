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
                [
                    'path' => '*.url',
                    'adapter' => function ($resource) {
                        // Github api need an user agent
                        $context = stream_context_create(['http' => ['header' => 'User-Agent: jobflow']]);

                        return file_get_contents($resource, false, $context);
                    }
                ]
            )
            ->add(
                'get_user_url',
                'callback_transformer',
                array(
                    'callback' => function($data, $target) {
                        $target['url'] = $data->getValue().'?access_token=236b93940ce523226035931f67d2de6bcc1aeab9';

                        return $target;
                    }
                )
            )
            ->add(
                'users_loader',
                'pipe_loader',
                [
                    'forward' => 'url'
                ]
            )
            ->add(
                'email_extractor',
                'json_extractor',
                [
                    'adapter' => function($resource) {
                        $context = stream_context_create(['http' => ['header' => 'User-Agent: jobflow']]);

                        return file_get_contents($resource, false, $context);
                    }
                ]
            )
            ->add(
                'get_user_email',
                'callback_transformer',
                array(
                    'callback' => function($data, $target) {
                        $value = $data->getValue();

                        if (property_exists($value, 'email') && strlen($value->email)) {
                            return $value->email."\n";
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
        $resolver->setDefaults([
            'context' => [
                'limit' => 15
            ]
        ]);
    }

    public function getName()
    {
        return 'github_email';
    }
}