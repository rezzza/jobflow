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
        $execution = $event->getExecutionContext();

        $this->logger->info(
            sprintf(
                '[%s] [%s] : Start to execute',
                $job->getParent()->getName(),
                $job->getName()
            ),
            $execution->getContextOptions()
        );
    }

    public function logEnd(JobEvent $event)
    {
        $job = $event->getJob();

        $this->logger->info(sprintf(
            '[%s] [%s] : End to execute',
            $job->getParent()->getName(),
            $job->getName()
        ));
    }
}
