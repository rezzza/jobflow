<?php

namespace Rezzza\JobFlow\Io;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Input extends AbstractStream
{
    public function read()
    {
        return $this->getWrapper()->read($this->parts['path']);
    }

    /**
     * All input except the first one should have a job scheme
     */
    public function isFirstStep()
    {
        return $this->parts['scheme'] !== 'job';
    }
}