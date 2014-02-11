<?php

namespace Rezzza\Jobflow\Io;

abstract class AbstractStream
{
    protected $driver;

    public function __construct(Driver\DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function getDriver()
    {
        return $this->driver;
    }
}
