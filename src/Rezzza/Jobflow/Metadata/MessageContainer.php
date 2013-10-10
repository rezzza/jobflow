<?php

namespace Rezzza\Jobflow\Metadata;

class MessageContainer
{
    protected $message;

    public function __construct($message = null)
    {
        $this->message = $message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getMetadata($name, $offset = 0)
    {
        $offset = $offset + $this->message->context->getOption('offset');
        
        return $this->message->metadata[$name][$offset];
    }
}