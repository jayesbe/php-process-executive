<?php
/*
 * This file is part of the php-process-executive package.
 *
 * (c) Jayes <jayesbe@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ProcessExecutive;

/**
 * Interface for the process executive to control its operation
 *
 * @author Jayes <jayesbe@users.noreply.github.com>
 */
interface ExecutiveControl
{

    /**
     * Close parent resources
     */
    public function closeResources();

    /**
     * Reload parent resources
     */
    public function reloadResources();

    /**
     * Returns the max number of processes the executive will allow to be executed at once
     *
     * @return int
     */
    public function getMaxProcesses();

    /**
     * Return's an item or items to process from the queue
     *
     * @return mixed
     */
    public function getProcessItem(&$queue);

    /**
     * Executes in the child process
     */
    public function executeChildProcess($item);
}
