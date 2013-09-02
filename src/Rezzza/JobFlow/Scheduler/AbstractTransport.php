<?php

namespace Rezzza\JobFlow\Scheduler;

abstract class AbstractTransport implements TransportInterface
{
     /**
     * @var JobMessage[]
     */
    protected $messages = array();
    
    public function addMessage($msg, $name = null)
    {
        $this->messages[] = $msg;

        return $this;
    }

    public function getMessage()
    {
        return array_shift($this->messages);
    }
}