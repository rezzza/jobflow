<?php

namespace Rezzza\JobFlow\Io;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Input extends AbstractStream
{
    protected $iterator;

    public function getWrapper($etl)
    {
        $class = $etl['extractor'];

        return new $class($this->getDsn());
    }
}