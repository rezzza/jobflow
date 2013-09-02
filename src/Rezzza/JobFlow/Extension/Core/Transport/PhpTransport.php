<?php

namespace Rezzza\JobFlow\Extension\Core\Transport;

use Rezzza\JobFlow\Scheduler\AbstractTransport;

class PhpTransport extends AbstractTransport
{
    public $result = null;
    
    public function store($result)
    {
        $this->addMessage($result);
    }

    public function getName()
    {
        return 'php';
    }
}