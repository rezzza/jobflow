<?php

namespace Rezzza\Jobflow;

/**
 * Output for execute method in JobType
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobOutput
{
    private $destination;

    private $data = array();

    private $pipe;

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
        if (null === $this->getDestination()) {
            $this->data[] = $result;

            return;
        }

        if ($this->getDestination() instanceof Pipe) {
            if (null === $this->pipe) {
                $this->pipe = $this->getDestination();
            }

            $this->pipe->addParam($result);

            return;
        }

        return $this->getDestination()->load($result, new \Knp\ETL\Context\Context);
    }

    public function getPipe()
    {
        return $this->pipe;
    }

    public function getData()
    {
        return $this->data;
    }
}