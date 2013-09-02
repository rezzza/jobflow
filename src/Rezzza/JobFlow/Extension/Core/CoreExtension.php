<?php

namespace Rezzza\JobFlow\Extension\Core;

use Rezzza\JobFlow\Extension\BaseExtension;

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