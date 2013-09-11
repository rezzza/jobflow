<?php

namespace Rezzza\JobFlow\Extension\Core\Transport;

use Rezzza\JobFlow\JobMessage;
use Rezzza\JobFlow\Scheduler\TransportInterface;

class PhpTransport implements TransportInterface
{
    /**
     * @var JobMessage[]
     */
    protected $messages = array();
    
    public function addMessage(JobMessage $msg)
    {
        $this->messages[] = $msg;

        return $this;
    }

    public function getMessage()
    {
        return array_shift($this->messages);
    }

    public function getName()
    {
        return 'php';
    }
}