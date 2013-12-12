<?php

namespace Rezzza\Jobflow;

class JobPayload implements \ArrayAccess, \IteratorAggregate
{
    /**
     * JobData[]
     */
    public $datas = array();

    public function store($data)
    {
        $this->datas[] = $data;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->datas);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->datas);
    }

    public function offsetGet($offset)
    {
        return $this->datas[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->datas[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->datas[$offset]);
    }
}