<?php

namespace Rezzza\JobFlow;

class JobOutput
{
    private $destination;

    private $data = array();

    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function write($result)
    {
        if (null !== $this->getDestination()) {
            return $this->getDestination()->load($result, new \Knp\ETL\Context\Context);
        }

        $this->data[] = $result;
    }

    public function getData()
    {
        return $this->data;
    }
}