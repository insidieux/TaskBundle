<?php
namespace TaskBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class FilterTaskLockEvent
 * @package TaskBundle\Event
 */
class FilterTaskLockEvent extends Event
{
    /**
     *
     */
    const NAME = 'task.lock';

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var int
     */
    private $worker;

    /**
     * BeforeTaskLockEvent constructor.
     * @param string $namespace
     * @param int    $worker
     */
    public function __construct(string $namespace, int $worker)
    {
        $this->namespace = $namespace;
        $this->worker = $worker;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return int
     */
    public function getWorker(): int
    {
        return $this->worker;
    }
}
