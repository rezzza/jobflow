<?php

namespace Rezzza\JobFlow\Io;

class Output extends AbstractStream
{
    protected $loader;

    public function getWrapper($etl)
    {
        $class = $etl['loader'];
        $file = new \SplFileObject($this->getDsn(), 'a+');

        return new $class($file);
    }
}