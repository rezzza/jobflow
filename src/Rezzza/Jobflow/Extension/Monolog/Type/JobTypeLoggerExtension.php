<?php

namespace Rezzza\Jobflow\Extension\Monolog\Type;

use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Rezzza\Jobflow\AbstractJobTypeExtension;
use Rezzza\Jobflow\Extension\Monolog\EventListener\LoggerListener;
use Rezzza\JobFlow\JobBuilder;

class JobTypeLoggerExtension extends AbstractJobTypeExtension
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->addEventSubscriber(new LoggerListener($this->logger))
        ;
    }

    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'logger' => $this->logger
        ]);
    }

    public function getExtendedType()
    {
        return 'job';
    }
}
