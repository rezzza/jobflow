<?php

namespace Rezzza\JobFlow;

use Rezzza\JobFlow\Extension\Core\CoreExtension;

/**
 * For standalone use
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
final class Jobs
{
    /**
     * Creates a job factory with the default configuration.
     *
     * @return JobFactory The job factory.
     */
    public static function createJobFactory()
    {
        return self::createJobFactoryBuilder()->getJobFactory();
    }

    /**
     * Creates a form factory builder with the default configuration.
     *
     * @return JobFactoryBuilder The job factory builder.
     */
    public static function createJobFactoryBuilder()
    {
        $builder = new JobFactoryBuilder();
        $builder->addExtension(new CoreExtension());

        return $builder;
    }

    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }
}