<?php

namespace Rezzza\Jobflow\Metadata;

class Metadata implements \ArrayAccess
{
    protected $values;

    public function __construct(array $values = array())
    {
        $this->values = $values;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->values[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }
}