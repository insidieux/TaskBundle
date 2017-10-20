<?php

namespace TaskBundle\Services;

use \Doctrine\ORM\EntityManager;
use \TaskBundle\Entity\Task;
use \TaskBundle\Handler\AbstractHandler;

/**
 * Class Pusher
 *
 * @package TaskBundle\Services
 */
class Pusher
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Pusher constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param AbstractHandler $handler
     * @param string          $namespace
     * @param int             $item
     *
     * @return Task
     */
    public function push(AbstractHandler $handler, string $namespace, int $item = 0)
    {
        $handler->tearDown();
        $handler->unsetContainer();

        $task = new Task;
        $task->setNamespace($namespace);
        $task->setItem($item);
        $task->setHandler($handler);

        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush($task);

        return $task;
    }
}

