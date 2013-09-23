<?php

namespace Rezzza\Jobflow\Extension\Monolog;

use Rezzza\Jobflow\Extension\BaseExtension;

class MonologExtension extends BaseExtension
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function loadTypeExtensions()
    {
        return array(
            new Type\JobTypeLoggerExtension($this->logger)
        );
    }
}