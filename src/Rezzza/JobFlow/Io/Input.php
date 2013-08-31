<?php

namespace Rezzza\JobFlow\Io;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Input extends AbstractStream
{
    protected $iterator;

    public function getIterator($etl)
    {
        $class = $etl['extractor'];

        return new $class($this->getDsn());
    }

    /**
     * All input except the first one should have a job scheme
     */
    public function isFirstStep()
    {
        return $this->parts['scheme'] !== 'job';
    }
}