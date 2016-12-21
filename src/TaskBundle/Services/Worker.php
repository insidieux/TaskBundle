<?php
namespace TaskBundle\Services;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TaskBundle\Event\FilterTaskLockEvent;
use TaskBundle\Exceptions\FinishError;
use TaskBundle\Exceptions\FinishRetry;
use TaskBundle\Exceptions\FinishSkip;
use TaskBundle\Exceptions\FinishSuccess;

/**
 * Class Worker
 * @package TaskBundle\Services
 */
class Worker extends ContainerAwareCommand
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var Locker
     */
    private $locker;

    /**
     * Worker constructor.
     * @param string $namespace
     * @param Locker $locker
     */
    public function __construct(Locker $locker, string $namespace)
    {
        $this->locker = $locker;
        $this->namespace = $namespace;
        parent::__construct(sprintf('task:worker:%s', $namespace));
    }

    /**
     * @return Locker
     */
    private function getLocker(): Locker
    {
        return $this->locker;
    }

    /**
     * @return string
     */
    private function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription("Process task queue with namespace \"{$this->getNamespace()}\"")
            ->addOption('id', 'id', InputOption::VALUE_REQUIRED, 'Worker identifier', 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = new FilterTaskLockEvent($this->getNamespace(), $input->getOption('id'));
        $this->getContainer()->get('event_dispatcher')->dispatch(FilterTaskLockEvent::NAME, $event);

        if ($event->isPropagationStopped() === true) {
            $output->writeln("Another service asked to stop process queue");
            return;
        }

        $task = $this->getLocker()->lock($this->getNamespace(), $input->getOption('id'));
        if (!$task) {
            $output->writeln("Empty tasks queue, exit");
            return;
        }

        $output->writeln("Task #{$task->getId()}. Start worker");
        $handler = $task->getHandler();
        try {
            $handler->setContainer($this->getContainer());
            $handler->setUp();
            $handler->perform();
            $task->setState($task::STATE_DONE_OK);
            $output->writeln("Task #{$task->getId()} completed successfully");
        } catch (FinishSuccess $exception) {
            // Task force finished success
            $task->setState($task::STATE_DONE_OK);
        } catch (FinishRetry $exception) {
            // Task force finished retry later (sleep and wait something)
            $task->setState($task::STATE_WAIT);
            $output->writeln("Task #{$task->getId()} got sleep command");
        } catch (FinishSkip $exception) {
            // Task force finished skip (for some reasons)
            $task->setState($task::STATE_SKIP);
            $output->writeln("Task #{$task->getId()} skipped with message {$exception->getMessage()}");
        } catch (\Throwable $exception) {
            // Task force finished with error or some other exception throw
            $task->setError($exception->getMessage());
            $task->setState($task::STATE_DONE_BAD);
            $output->writeln("Task #{$task->getId()} completed with errors");
            // Just to make dev more happy - decorate exception ass force finish error exception
            if (!($exception instanceof FinishError)) {
                $message = sprintf("\"%s\": %s (%s:%s)",
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                );
                $exception = new FinishError($message, $exception->getCode(), $exception);
            }
            // pass exception to handler - last chance to recovery
            $handler->handleExceptions($exception);
        }

        $output->writeln("Task #{$task->getId()}. Stop worker. State {$task->getState()}");

        $handler->tearDown();
        $handler->unsetContainer();

        // It's a magic, it's a maaaagic! Doctrine cannot check serialized object was changed
        // So if you want to save changed object in serialized form - CLONE (WTF?!!)
        $task->setHandler(clone $handler);
        $task->setWorker(0);
        $this->getLocker()->unlock($task);
    }
}
