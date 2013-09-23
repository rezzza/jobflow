<?php

namespace Rezzza\Jobflow\Extension\Monolog\Type;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\AbstractJobTypeExtension;
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
        $builder->setLogger($this->logger);
    }

    public function getExtendedType()
    {
        return 'job';
    }
}