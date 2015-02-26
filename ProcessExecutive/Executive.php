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
  private 
  
  $control,
  
  $procs,
  
  $maxProcs;
  
  public function __construct(ExecutiveControl $control)
  {
    $this->control = $control;
    $this->procs = array();
    $this->maxProcs = $control->getMaxProcesses();
  }
}
