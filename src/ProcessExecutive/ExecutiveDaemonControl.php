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
interface ExecutiveDaemonControl extends ExecutiveControl
{

    /**
     * return a queue of items
     *
     * @return array
     */
    public function getQueue();

    /**
     * Should the daemon stop ?
     *
     * @return boolean true to stop | false to continue
     */
    public function stop();
}
