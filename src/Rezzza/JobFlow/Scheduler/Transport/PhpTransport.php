<?php

namespace Rezzza\JobFlow\Scheduler\Transport;

class PhpTransport extends AbstractTransport
{
    public $result = null;
    
    public function store($result)
    {
        $this->addMessage($result);
    }
}