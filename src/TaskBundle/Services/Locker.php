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
     * @return TaskRepository
     */
    private function getEntityRepository()
    {
        return $this->getEntityManager()->getRepository('TaskBundle:Task');
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

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->update('TaskBundle:Task', 'task')
            ->set('task.state', ':set_state')
            ->set('task.worker', ':set_worker')
            ->where('task.state = :query_state')
            ->andWhere('task.worker = :query_worker')
            ->andWhere('task.namespace = :query_namespace')
            ->setMaxResults(1)
            ->setParameter('set_state', Task::STATE_PROCESS)
            ->setParameter('set_worker', $worker)
            ->setParameter('query_namespace', $namespace)
            ->setParameter('query_state', Task::STATE_WAIT)
            ->setParameter('query_worker', 0)
            ->getQuery();
        $lock = $query->execute();
        if ($lock == 0) {
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

