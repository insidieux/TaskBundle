<?php
namespace TaskBundle\Services;

use Doctrine\ORM\EntityManager;
use TaskBundle\Entity\Task;
use TaskBundle\Repository\TaskRepository;

/**
 * Class Locker
 * @package TaskBundle\Services
 */
class Locker
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Locker constructor.
     * @param EntityManager  $entityManager
     * @param TaskRepository $entityRepository
     */
    public function __construct(EntityManager $entityManager, TaskRepository $entityRepository)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $entityRepository;
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return TaskRepository
     */
    private function getEntityRepository()
    {
        return $this->entityRepository;
    }

    /**
     * @param string $namespace
     * @param int    $worker
     * @return bool|Task
     */
    public function lock(string $namespace, int $worker)
    {
        $task = $this->getEntityRepository()->byNamespaceAndWorker($namespace, $worker);
        // catch moment when our worker is already on work
        if ($task !== null) {
            return false;
        }

        $query = "UPDATE tasks_queue SET state = :set_state, worker = :set_worker WHERE id IN (SELECT id FROM tasks_queue WHERE state = :query_state AND worker = :query_worker AND namespace = :query_namespace ORDER BY updated ASC LIMIT 1)";
        $parameters = [
            'set_state'       => Task::STATE_PROCESS,
            'set_worker'      => $worker,
            'query_namespace' => $namespace,
            'query_state'     => Task::STATE_WAIT,
            'query_worker'    => 0,
        ];

        $lock = $this->getEntityManager()
            ->getConnection()
            ->executeUpdate($query, $parameters);
        if ($lock === 0) {
            return false;
        }

        $task = $this->getEntityRepository()->byNamespaceAndWorker($namespace, $worker);
        if ($task === null) {
            return false;
        }

        return $task;
    }

    /**
     * @param Task $task
     */
    public function unlock(Task $task)
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush($task);
    }
}
