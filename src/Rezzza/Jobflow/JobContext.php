<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Keeps state for the job execution
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobContext implements JobContextInterface
{
    /**
     * The job id we run
     *
     * @var string
     */
    public $jobId;

    /**
     * Current job name in execution
     *
     * @var string
     */
    private $current;

    /**
     * Steps already executed
     *
     * @var array
     */
    private $steps = array();

    /**
     * Step which start this context.
     * At the end of loops, we will requeue to this step
     *
     * @var string
     */
    private $origin;

    /**
     * IO
     */
    private $io;

    /**
     * @var array
     */
    private $options = array();

    public $jobOptions = array();

    public $transport;

    public function __construct(
        $jobId,
        $io = null,
        $current = null,
        array $options = [],
        array $jobOptions = [],
        $transport = null
    )
    {
        $this->jobId = $jobId;
        $this->io = $io;
        $this->current = $current;
        $this->jobOptions = $jobOptions;
        $this->transport = $transport;
        $this->initOptions($options);

        if (null === $this->origin) {
            $this->origin = $current;
        }
    }

    /**
     * Adds step to keep trace
     */
    public function completeStep($step)
    {
        $this->steps[] = $step;
    }

    public function completeCurrent()
    {
        $this->completeStep($this->current);
        $this->current = null;
    }

    public function moveTo($next)
    {
        $this->completeCurrent();
        $this->current = $next;
    }

    public function reset()
    {
        $this->moveTo($this->origin);
    }

    /**
     * Checks if JobContext has already traveled
     *
     * @return boolean
     */
    public function isStarting()
    {
        return count($this->steps) === 0;
    }

    /**
     * @return string
     */
    public function getMessageName()
    {
        return sprintf('%s.%s', $this->jobId, $this->current);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'offset' => 0,
            'limit' => 50,
            'total' => null,
            'max' => null
        ));
    }

    public function initOptions(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function tick()
    {
        $this->options['offset'] += $this->options['limit'];
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function getCurrent()
    {
        return $this->current;
    }

    public function getIo()
    {
        return $this->io;
    }

    public function getOrigin()
    {
        return $this->origin;
    }
}