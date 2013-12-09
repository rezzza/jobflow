<?php

namespace Rezzza\Jobflow\Extension\Core\Transport;

use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\Scheduler\TransportInterface;

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
