<?php

namespace Rezzza\Jobflow\Extension\Monolog\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Rezzza\Jobflow\Event\JobEvent;
use Rezzza\Jobflow\Event\JobEvents;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class LoggerListener implements EventSubscriberInterface
{
    private $logger;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            JobEvents::PRE_EXECUTE => 'logStart',
            JobEvents::POST_EXECUTE => 'logEnd'
        );
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logStart(JobEvent $event)
    {
        $job = $event->getJob();

        $this->logger->info(sprintf(
            'Start to execute Job [%s] : %s',
            $job->getParent()->getName(),
            $job->getName()
        ));
    }

    public function logEnd(JobEvent $event)
    {
        $job = $event->getJob();

        $this->logger->info(sprintf(
            'End to execute Job [%s] : %s',
            $job->getParent()->getName(),
            $job->getName()
        ));
    }
}
