<?php

namespace Rezzza\Jobflow;

/**
 * Input for execute method in JobType
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobInput extends JobStream
{
    public function getData()
    {
        return $this->message->data;
    }
}