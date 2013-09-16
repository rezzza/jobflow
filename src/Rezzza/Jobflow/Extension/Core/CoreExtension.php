<?php

namespace Rezzza\Jobflow\Extension\Core;

use Rezzza\Jobflow\Extension\BaseExtension;

class CoreExtension extends BaseExtension
{
    public function loadTypes()
    {
        return array(
            new Type\JobType()
        );
    }

    public function loadTransports()
    {
        return array(
            new Transport\PhpTransport()
        );
    }
}