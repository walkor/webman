<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace process;

use Workerman\Timer;
use Workerman\Worker;

/**
 * Class FileMonitor
 * @package process
 */
class Monitor
{
    /**
     * @var array
     */
    protected $_paths = [];

    /**
     * @var array
     */
    protected $_extensions = [];

    /**
     * FileMonitor constructor.
     * @param $monitor_dir
     * @param $monitor_extensions
     * @param $memory_limit
     */
    public function __construct($monitor_dir, $monitor_extensions, $memory_limit = null)
    {
        $this->_paths = (array)$monitor_dir;
        $this->_extensions = $monitor_extensions;
        if (!Worker::getAllWorkers()) {
            return;
        }
        $disable_functions = explode(',', ini_get('disable_functions'));
        if (in_array('exec', $disable_functions, true)) {
            echo "\nMonitor file change turned off because exec() has been disabled by disable_functions setting in " . PHP_CONFIG_FILE_PATH . "/php.ini\n";
        } else {
            if (!Worker::$daemonize) {
                Timer::add(1, function () {
                    $this->checkAllFilesChange();
                });
            }
        }

        $memory_limit = $this->getMemoryLimit($memory_limit);
        if ($memory_limit && DIRECTORY_SEPARATOR === '/') {
            Timer::add(60, [$this, 'checkMemory'], [$memory_limit]);
        }
    }

    /**
     * @param $monitor_dir
     */
    public function checkFilesChange($monitor_dir)
    {
        static $last_mtime;
        if (!$last_mtime) {
            $last_mtime = time();
        }
        clearstatcache();
        if (!is_dir($monitor_dir)) {
            if (!is_file($monitor_dir)) {
                return;
            }
            $iterator = [new \SplFileInfo($monitor_dir)];
        } else {
            // recursive traversal directory
            $dir_iterator = new \RecursiveDirectoryIterator($monitor_dir, \FilesystemIterator::FOLLOW_SYMLINKS);
            $iterator = new \RecursiveIteratorIterator($dir_iterator);
        }
        foreach ($iterator as $file) {
            /** var SplFileInfo $file */
            if (is_dir($file)) {
                continue;
            }
            // check mtime
            if ($last_mtime < $file->getMTime() && in_array($file->getExtension(), $this->_extensions, true)) {
                $var = 0;
                exec(PHP_BINARY . " -l " . $file, $out, $var);
                if ($var) {
                    $last_mtime = $file->getMTime();
                    continue;
                }
                $last_mtime = $file->getMTime();
                echo $file . " update and reload\n";
                // send SIGUSR1 signal to master process for reload
                if (DIRECTORY_SEPARATOR === '/') {
                    posix_kill(posix_getppid(), SIGUSR1);
                } else {
                    return true;
                }
                break;
            }
        }
    }

    /**
     * @return bool
     */
    public function checkAllFilesChange()
    {
        foreach ($this->_paths as $path) {
            if ($this->checkFilesChange($path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $memory_limit
     * @return void
     */
    public function checkMemory($memory_limit)
    {
        $ppid = posix_getppid();
        $children_file = "/proc/$ppid/task/$ppid/children";
        if (!is_file($children_file) || !($children = file_get_contents($children_file))) {
            return;
        }
        foreach (explode(' ', $children) as $pid) {
            $pid = (int)$pid;
            $status_file = "/proc/$pid/status";
            if (!is_file($status_file) || !($status = file_get_contents($status_file))) {
                continue;
            }
            $mem = 0;
            if (preg_match('/VmRSS\s*?:\s*?(\d+?)\s*?kB/', $status, $match)) {
                $mem = $match[1];
            }
            $mem = (int)($mem / 1024);
            if ($mem >= $memory_limit) {
                posix_kill($pid, SIGINT);
            }
        }
    }

    /**
     * Get memory limit
     * @return float
     */
    protected function getMemoryLimit($memory_limit)
    {
        if ($memory_limit === 0) {
            return 0;
        }
        $use_php_ini = false;
        if (!$memory_limit) {
            $memory_limit = ini_get('memory_limit');
            $use_php_ini = true;
        }

        if ($memory_limit == -1) {
            return 0;
        }
        $unit = $memory_limit[strlen($memory_limit) - 1];
        if ($unit == 'G') {
            $memory_limit = 1024 * (int)$memory_limit;
        } else if ($unit == 'M') {
            $memory_limit = (int)$memory_limit;
        } else if ($unit == 'K') {
            $memory_limit = (int)($memory_limit / 1024);
        } else {
            $memory_limit = (int)($memory_limit / (1024 * 1024));
        }
        if ($memory_limit < 30) {
            $memory_limit = 30;
        }
        if ($use_php_ini) {
            $memory_limit = (int)(0.8 * $memory_limit);
        }
        return $memory_limit;
    }
}
