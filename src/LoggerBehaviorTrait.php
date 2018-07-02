<?php

namespace Norsys\LogsBundle;

use Psr\Log\LoggerInterface;

/**
 * Basic Implementation of LoggerBehavior.
 */
trait LoggerBehaviorTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Setter for logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Getter for logger.
     *
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->logger;
    }
}
