<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Psr\Log\LoggerInterface;
use Rezzza\Jobflow\Scheduler\JobGraph;

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

    public $jobUniqId;

    /**
     * IO
     */
    public $io;

    public $jobOptions = array();

    public $metadata;

    public $terminated = false;

    /**
     * Current job child name in execution
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

    private $options = array();

    public function __construct(
        $jobId,
        $io = null,
        $current = null,
        array $options = [],
        array $jobOptions = [],
        $metadata = null
    )
    {
        $this->jobId = $jobId;
        $this->jobUniqId = $jobId . '.' . uniqid();
        $this->io = $io;
        $this->current = $current;
        $this->jobOptions = $jobOptions;
        $this->metadata = $metadata;
        $this->initOptions($options);

        if (null === $this->origin) {
            $this->origin = $current;
        }
    }

    public function currentStep($step)
    {
        $this->current = $step;
    }

    public function moveTo($next)
    {
        $this->completeCurrent();
        $this->currentStep($next);
    }

    public function reset()
    {
        $this->terminated = false;
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

    public function shouldRequeue()
    {
        // While we don't know the total we continue to requeue...
        if ($this->options['total'] === null) {
            return true;
        }

        return (is_integer($this->options['total']) && $this->options['total'] > $this->options['offset']);
    }

    public function isTerminated()
    {
        return true === $this->terminated;
    }

    public function terminate()
    {
        $this->terminated = true;
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

    public function logState(LoggerInterface $logger)
    {
        $step = $this->current ?: 'starting';

        $logger->info(
            sprintf(
                '[%s] [%s] : New message',
                $this->jobId,
                $step
            ),
            $this->getOptions()
        );
    }

    public function currentChild(Job $job)
    {
        return $job->get($this->current);
    }

    public function initGraph(JobGraph $graph)
    {
        $graph->move($this->current);
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     */
    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @param string $key
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Adds step to keep trace
     * @param string $step
     */
    protected function completeStep($step)
    {
        $this->steps[] = $step;
    }

    protected function completeCurrent()
    {
        $this->completeStep($this->current);
    }
}
