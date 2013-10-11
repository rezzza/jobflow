<?php

namespace Rezzza\Jobflow;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Rezzza\Jobflow\Extension\Pipe\Pipe;

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

    public function getMetadata()
    {
        return $this->message->metadata;
    }
}