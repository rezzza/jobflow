<?php

namespace Rezzza\JobFlow\Scheduler\Transport;

use Rezzza\JobFlow\JobMessage;

class PhpTransport extends AbstractTransport
{
    public $result = null;
    
    public function store($result)
    {
        $this->addMessage($result);
    }
}