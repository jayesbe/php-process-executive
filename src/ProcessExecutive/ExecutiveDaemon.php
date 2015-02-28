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
 * The ExecutiveDaemon will use the provided control interface to launch child processes indefinitely.
 *
 * @author Jayes <jayesbe@users.noreply.github.com>
 */
class ExecutiveDaemon extends Executive
{

    public function __construct(ExecutiveDaemonControl $control)
    {
        parent::__construct($control);
    }

    public function run($sleepTime = 60)
    {
        while (1) {
            $queue = $this->getControl()->getQueue();
            
            $this->execute($queue);
            
            if ($this->getControl()->stop()) {
                break;
            }
            
            if ($sleepTime == 0) {
                break;
            }
            $this->real_sleep($sleepTime);
        }
        
        // daemon is being exited..
        if ($this->areResourcesClosed()) {
            $this->getControl()->reloadResources();
        }
        
        while (!empty($this->procs)) {
            $pid = array_shift($this->procs);
            $waitpid = pcntl_waitpid($pid, $status, WNOHANG | WUNTRACED);
            if ($waitpid == 0) {
                array_push($this->procs, $pid);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \ProcessExecutive\Executive::getControl()
     *
     * @return ExecutiveDaemonControl
     */
    protected function getControl()
    {
        return $this->getControl();
    }
}