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

    private $end = false;

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

        $this->getDestination()->load($result, new \Knp\ETL\Context\Context);
    }

    public function finish()
    {
        if ($this->getDestination() instanceof \Knp\ETL\Loader\Doctrine\ORMLoader) {
            $this->getDestination()->flush(new \Knp\ETL\Context\Context);
            $this->getDestination()->clear(new \Knp\ETL\Context\Context);
        }
    }

    public function getPipe()
    {
        return $this->pipe;
    }

    public function getData()
    {
        return $this->data;
    }

    public function end()
    {
        $this->end = true;
    }

    public function isEnded()
    {
        return $this->end;
    }
}