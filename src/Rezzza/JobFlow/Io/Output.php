<?php

namespace Rezzza\JobFlow\Io;

class Output extends AbstractStream
{
    protected $loader;

    protected $data;

    public function getLoader($etl)
    {
        $class = $etl['loader'];
        $file = new \SplFileObject($this->getDsn(), 'a+');

        return new $class($file);
    }

    public function write($result)
    {
        
    }
}