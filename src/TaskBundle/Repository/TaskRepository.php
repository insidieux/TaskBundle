<?php
namespace TaskBundle\Repository;

use Doctrine\ORM\EntityRepository;
use TaskBundle\Entity\Task;

/**
 * Class TaskRepository
 * @package TaskBundle\Repository
 *
 * @method Task findOneBy(array $criteria, array $orderBy = null)
 * @method Task findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends EntityRepository
{
    /**
     * @param string $namespace
     * @param int    $item
     * @return Task
     */
    public function byNamespaceAndItem(string $namespace, int $item)
    {
        return $this->findBy([
            'namespace' => $namespace,
            'item'      => $item,
        ]);
    }

    /**
     * @param string $namespace
     * @param int    $worker
     * @return Task
     */
    public function byNamespaceAndWorker(string $namespace, int $worker)
    {
        return $this->findOneBy([
            'namespace' => $namespace,
            'worker'    => $worker,
        ]);
    }
}
