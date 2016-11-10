<?php
namespace TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use TaskBundle\Handler\AbstractHandler;

/**
 * Class Task
 * @package TaskBundle\Entity
 *
 * @ORM\Entity(repositoryClass="TaskBundle\Repository\TaskRepository")
 * @ORM\Table(
 *     name="tasks_queue",
 *     indexes={
 *      @ORM\Index(name="lock_idx", columns={"namespace", "state", "worker"}),
 *      @ORM\Index(name="item_idx", columns={"namespace", "item"})
 *     }
 * )
 */
class Task
{
    const STATE_WAIT     = 0;
    const STATE_PROCESS  = 1;
    const STATE_DONE_OK  = 2;
    const STATE_DONE_BAD = 3;
    const STATE_SKIP     = 4;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned": true})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="namespace", type="string", length=255, nullable=false, options={"default":""})
     */
    private $namespace;

    /**
     * @var int
     *
     * @ORM\Column(name="item", type="bigint", nullable=true, options={"unsigned": true, "default":0})
     */
    private $item = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="state", type="smallint", length=5, nullable=false, options={"unsigned": true, "default":0})
     */
    private $state = self::STATE_WAIT;

    /**
     * @var int
     *
     * @ORM\Column(name="worker", type="integer", nullable=true, options={"unsigned": true, "default":0})
     */
    private $worker = 0;

    /**
     * @var AbstractHandler
     *
     * @ORM\Column(name="handler", type="object")
     */
    private $handler;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $error = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return Task
     */
    public function setNamespace(string $namespace): Task
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return int
     */
    public function getItem(): int
    {
        return $this->item;
    }

    /**
     * @param int $item
     * @return Task
     */
    public function setItem(int $item): Task
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     * @return Task
     */
    public function setState(int $state): Task
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return int
     */
    public function getWorker(): int
    {
        return $this->worker;
    }

    /**
     * @param int $worker
     * @return Task
     */
    public function setWorker(int $worker): Task
    {
        $this->worker = $worker;
        return $this;
    }

    /**
     * @return AbstractHandler
     */
    public function getHandler(): AbstractHandler
    {
        return $this->handler;
    }

    /**
     * @param AbstractHandler $handler
     * @return Task
     */
    public function setHandler(AbstractHandler $handler): Task
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return Task
     */
    public function setError(string $error): Task
    {
        $this->error = $error;
        return $this;
    }
}
