<?php

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\Io;
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
                    'path' => '*.url',
                    'io' => $this->getIo()
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
                'file_loader',
                array(
                    'io' => $this->getIo()
                )
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

    public function getIo()
    {
        return new Io\IoDescriptor(
            new Io\Input('https://api.github.com/repos/symfony/console/stargazers?access_token=236b93940ce523226035931f67d2de6bcc1aeab9'),
            new Io\Output('file:///'.__DIR__."/../temp/email.csv")
        );
    }

    public function getName()
    {
        return 'github_email';
    }
}