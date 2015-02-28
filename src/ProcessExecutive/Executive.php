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
 * The Executive will use the provided control interface to launch child processes.
 *
 * @author Jayes <jayesbe@users.noreply.github.com>
 */
class Executive
{
    private $control;
    
    private $maxProcs; 

    private $resourcesClosed;

    protected $procs;

    public function __construct(ExecutiveControl $control)
    {
        $this->control = $control;
        $this->procs = array();
        $this->maxProcs = $control->getMaxProcesses();
        $this->resourcesClosed = false;
    }

    /**
     *
     * @return ExecutiveControl
     */
    protected function getControl()
    {
        return $this->control;
    }

    /**
     *
     * @return boolean
     */
    protected function areResourcesClosed()
    {
        return $this->resourcesClosed;
    }

    /**
     *
     * @param array $queue            
     */
    public function execute(&$queue)
    {
        while (!empty($queue)) {
            
            // how many concurrent processes ?
            if (count($this->procs) < $this->maxProcs) {
                
                // we generally begin with our connections closed so our forks are connection clean
                if (! $this->areResourcesClosed()) {
                    $this->getControl()->closeResources();
                    $this->resourcesClosed = true;
                }
                
                $item = $this->getControl()->getProcessItem($queue);
                
                // fork
                $pid = pcntl_fork();
                if ($pid == - 1) {
                    // this should probably do something better
                    die('Could not fork ');
                }
                // parent.. immediately check the child's status
                else if ($pid) {
                    if (pcntl_waitpid($pid, $status, WNOHANG) == 0) {
                        $this->procs[$pid] = $pid;
                        continue; // until max_procs is reached
                    }
                }
                // child..
                else {
                    $this->getControl()->executeChildProcess($item);
                    exit(0);
                }
            }
            
            // reopen our connection if closed
            if ($this->areResourcesClosed()) {
                $this->getControl()->reloadResources();
                $this->resourcesClosed = false;
            }
            
            // loop our currently processing procs and clear out the completed ones
            foreach ( $this->procs as $pid => $pidval ) {
                $waitpid = pcntl_waitpid($pid, $status, WNOHANG | WUNTRACED);
                if ($waitpid == 0) {
                    continue;
                }
                unset($this->procs[$pid]);
            }
        }
        
        // we have to check here again if the connection is closed.
        if ($this->areResourcesClosed()) {
            $this->getControl()->reloadResources();
            $this->resourcesClosed = false;
        }
        
        // now clean up any remaining process
        while ( ! empty($this->procs) ) {
            $pid = array_shift($this->procs);
            $waitpid = pcntl_waitpid($pid, $status, WNOHANG | WUNTRACED);
            if ($waitpid == 0) {
                array_push($this->procs, $pid);
            }
            else {
                $this->real_sleep(1);
            }
        }
    }

    /**
     *
     * @param int $seconds            
     */
    protected function real_sleep($seconds)
    {
        $start = microtime(true);
        for ($i = 1; $i <= $seconds; $i ++) {
            @time_sleep_until($start + $i);
        }
    }
}
