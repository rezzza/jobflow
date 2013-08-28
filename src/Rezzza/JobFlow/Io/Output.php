<?php

namespace Rezzza\JobFlow\Io;

class Output extends AbstractStream
{
    public function write()
    {
        return $this->wrapper->getOutput($this->parts['path']);
    }
}