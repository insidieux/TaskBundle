<?php
namespace TaskBundle\Handler;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TaskBundle\Exceptions\FinishError;
use TaskBundle\Exceptions\FinishRetry;
use TaskBundle\Exceptions\FinishSkip;
use TaskBundle\Exceptions\FinishSuccess;

/**
 * Class AbstractHandler
 * @package TaskBundle\Handler
 */
abstract class AbstractHandler
{
    use ContainerAwareTrait;

    const STAGE_STATE_NEW      = 0;
    const STAGE_STATE_PROCESS  = 1;
    const STAGE_STATE_COMPLETE = 2;

    /**
     * Stages progress info
     *
     * @var array
     */
    public $stages = [];

    /**
     * Method to init handler state. Calls before perform
     *
     */
    public function setUp()
    {
    }

    /**
     * Method to perform handler operations
     */
    abstract public function perform();

    /**
     * Method to cleanup handler state.  Calls after perform
     */
    public function tearDown()
    {
    }

    /**
     * Helper method to finish task early with complete ok status
     *
     * @throws FinishSuccess
     */
    public function finishSuccess()
    {
        throw new FinishSuccess;
    }

    /**
     * Helper method to sleep task and try to run it later
     *
     * @throws FinishRetry
     */
    public function finishRetry()
    {
        throw new FinishRetry;
    }

    /**
     * Helper method to finish task early with complete bad status
     *
     * @param string $message error message
     *
     * @throws FinishError
     */
    public function finishError($message)
    {
        throw new FinishError($message);
    }

    /**
     * Helper method to finish task early with complete skip status
     *
     * @param string $message error message
     *
     * @throws FinishSkip
     */
    public function finishSkip($message)
    {
        throw new FinishSkip($message);
    }

    /**
     * Handle of non Queue exception
     *
     * @param FinishError $e
     */
    public function handleExceptions(FinishError $e)
    {
    }

    /**
     * @param $stage
     *
     * @return bool
     */
    public function isStageCompleted($stage)
    {
        return isset($this->stages[$stage]) && $this->stages[$stage] == self::STAGE_STATE_COMPLETE;
    }

    /**
     * @param $stage
     *
     * @return bool
     */
    public function isStageNotCompleted($stage)
    {
        return !$this->isStageCompleted($stage);
    }

    /**
     * @param $stage
     */
    public function setStageCompleted($stage)
    {
        $this->stages[$stage] = self::STAGE_STATE_COMPLETE;
    }

    /**
     * @param $stage
     *
     * @return bool
     */
    public function isStageInProcess($stage): bool
    {
        return isset($this->stages[$stage]) && $this->stages[$stage] == self::STAGE_STATE_PROCESS;
    }

    /**
     * @param $stage
     */
    public function setStageInProcess($stage)
    {
        $this->stages[$stage] = self::STAGE_STATE_PROCESS;
    }

    /**
     * Try to set stage state in process
     * - if stage already in process - return false
     * - if stage not in process - set state and return true
     *
     * @param $stage
     *
     * @return bool
     */
    public function tryStartStageProcess($stage): bool
    {
        if ($this->isStageInProcess($stage)) {
            return false;
        } else {
            $this->setStageInProcess($stage);
            return true;
        }
    }

    /**
     * Get Container object
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Remove container from object. Called after @tearDown
     */
    public function unsetContainer()
    {
        unset($this->container);
    }
}
