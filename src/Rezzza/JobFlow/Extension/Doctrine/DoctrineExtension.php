<?php

namespace Rezzza\JobFlow\Extension\RabbitMq;

use Rezzza\JobFlow\Extension\BaseExtension;

class DoctrineExtension extends BaseExtension
{
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function loadTypes()
    {
        return array(
            new Type\Loader\EntityLoader($this->doctrine)
        );
    }
}