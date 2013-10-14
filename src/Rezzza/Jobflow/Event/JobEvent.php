<?php

namespace Rezzza\Jobflow\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobEvent extends Event
{
    private $job;
    protected $message;

    public function __construct($job)
    {
        $this->job = $job;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
