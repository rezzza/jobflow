<?php

namespace Rezzza\Jobflow\Io\Driver;

class File implements DriverInterface
{
    protected $dsn;

    protected $fileInfo;

    public function __construct($dsn)
    {
        $this->dsn = $dsn;
        $this->initFileInfo($dsn);
    }

    private function initFileInfo($dsn)
    {
        $this->fileInfo = new \SplFileInfo($dsn);
    }

    public function getDsn()
    {
        return $this->dsn;
    }

    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    public function __sleep()
    {
        return array('dsn');
    }
}
