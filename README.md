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
------------

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