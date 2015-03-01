php-process-executive
=====================

Execute forked processing easily.

**Features**

  * Execute many concurrent child processes keeping the parent process clean. 
  * Execute as a daemon to keep the parent process alive. Combine with CRON and forever.js for an effective and lightweight PHP Daemon. 

**Requirements**

  * Process Control (PCNTL)

Installation
------------

In order to install php-process-executive you need the PHP Process Control Extension. 

### On Ubuntu

Open a command console and execute the
following command to install PCNTL:

```bash
$ pecl install pcntl
```

Usage
-----

Here is a sample implementation of a Symfony2 Command using ProcessExecutive

```php
namespace FooBar\AppBundle\Command;

// use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use FooBar\UserBundle\Entity\User;

use ProcessExecutive\Executive;
use ProcessExecutive\ExecutiveControl;

/**
 * GenerateUsers command for testing purposes. 
 * 
 * Will generate random users
 *
 * You could also extend from Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
 * to get access to the container via $this->getContainer().
 *
 * @author Jayesbe
 */
class GenerateUsersCommand extends ContainerAwareCommand implements ExecutiveControl
{
    const MAX_USERS = 5000000;
    
    private 
    
    $userSize,
    
    $totalGenerated,
    
    $output;
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->userSize = 0;
        $this->totalGenerated = 0;
        
        $this
            ->setName('foobar:generate:users')
            ->setDescription('Populate database with random users.')
            ->addArgument('size', InputArgument::OPTIONAL, 'Number of Users to generate', self::MAX_USERS)
            ->setHelp(<<<EOF
The <info>%command.name%</info> will populate the database with randomly generated users.
                    
The optional argument specifies the size of the population to generate (up to a maximum of 5 million):
        
<info>php %command.full_name%</info> 5000000
EOF
            );               
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $this->userSize = intval($input->getArgument('size'));
        if ($this->userSize > self::MAX_USERS) {
            $output->writeln("Attempted to populate with ".$this->userSize.' users. Max allowed '.self::MAX_USERS);
            return;
        }
        $output->writeln("Attempting to populate with ".$this->userSize.' users...');
        
        // we call these to make sure they are created in the parent
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();       
        
        // we need to create our Executive here
        $processor = new Executive($this);
        
        // your queue can be anything.
        $queue = array(0 => $this->userSize);
        
        // execute our queue
        $processor->execute($queue);
                
        $output->writeln(sprintf('Population Created: <comment>%s</comment> users!', $this->totalGenerated));
    }
    
    public function closeResources()
    {
        // close all db connections and memcache connections.
        // anything that requires the child to have its own resource since 
        // the parent cannot share its resources with its children. 
        $this->getContainer()->get('doctrine')->getManager()->getConnection()->close();
    }
    
    public function reloadResources()
    {
        $this->getContainer()->get('doctrine')->getManager()->getConnection()->connect();
    }
    
    public function getMaxProcesses()
    {
        // will create and maintain 8 concurrent processes
        return 8; 
    }
    
    public function getProcessItem(&$queue)
    {
        // handle your queue item.
        // since we are generating users and our queue only contains a count
        // we will generate a user id and return that as our item
        // we will then decrease the size of users we need to generate
        // the system will halt processing when the queue is empty
        
        if ($queue[0] == 0) {
            throw new \Exception('Empty Queue.');
        }
        
        $uid = uniqid("u",true);
        --$queue[0];
        $this->totalGenerated++;
        
        if ($queue[0] == 0) {
            $queue = null;
            $queue = array();
        }
        
        return $uid;
    }
    
    public function executeChildProcess($uid)
    {
        // now the main bits of processing we want done in each child.
        
        // get doctrine and entity manager and reload connection
        // this part must not use the parent process as we need to create all new object references for each child
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        
        // and a completely separate db connection per child
        $em->getConnection()->connect();
        
        $user = new User();
        $user->setEmail($uid.'@example.org');
        $user->setUsername($uid);
        $user->setPlainPassword($uid);
        $user->setEnabled(true);
        
        // if you use random number generate mt_rand() you need to seed for each child
        // otherwise the childs will all use the same seed from the parent. 
        // seed mt_rand
        // mt_srand();
        
        $em->persist($user);
        $em->flush();
        
        $this->output->writeln("User Generated: ".$user->getId());
    }
}
```

Notes
-----

Doctrine is known to continually increase memory in a CLI environment. Its recommended to use PDO. However if you really want to use Doctrine, the only efficient way is to make sure that memory is cleared out when youre done with it. We have been utilizing this same code for the past two years in production. We use it for a variety of applications in both single run and daemonized modes. We use it for both Symfony 1 / Doctrine 1 and Symfony 2 / Doctrine 2 with the same results. A parent process that doesn't move when it comes to memory consumption and cpu usage. We also combine our execution with 'nice' which allows us to control the process priority of the parent and child processes executed.

for example, the above Symfony2 Command can be run as 

```bash
nice -n 10 -- app/console "foobar:generate:users" -size=100
```

The performance will be based on how much work your child processes do and potentially how often they hit the disk. Vary the getMaxProcess() return value between 1, 2, 4, 8 or more to see how much you can eak out of it. 

The above Symfony2 Command is perfect to compare timing.

for example, on a simple i5 with 4GB of available memor, creating 100 users with:

```bash
time app/console foobar:generate:users 100
```

2 processes 

```bash
real	0m12.589s
user	0m12.875s
sys	0m2.936s
```

4 processes

```bash
real	0m10.658s
user	0m8.501s
sys	0m2.628s
```

8 processes

```bash
real	0m15.807s
user	0m9.249s
sys	0m2.757s
```

Most importantly is memory consumption. The above Symfony2 Command results in a parent process that consumes 40 MB of memory and does not move. The process can run indefinitely without chewing up all the memory in the system. 

LEGAL DISCLAIMER
----------------

This software is published under the MIT License, which states that:

> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
> IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
> FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
> AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
> LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
> OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
> SOFTWARE.

-----
