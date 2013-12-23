<?php

namespace Rezzza\Jobflow;

abstract class JobStream
{
    /**
     * @var JobMessage
     */
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}