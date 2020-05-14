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
namespace support\bootstrap;

use Webman\Bootstrap;
use Illuminate\Redis\RedisManager;

/**
 * Class Redis
 * @package support
 *
 * Keys method
 * @method static mixed get($key)
 * @method static bool set($key, $val, $timeout)
 * @method static int del(...$keys)
 * @method static int exists(...$keys)
 * @method static bool expire($key, $ttl)
 * @method static bool expireAt($key, $timestamp)
 *
 */
class Redis implements Bootstrap {

    /**
     * @var RedisManager
     */
    protected static $_manager = null;

    /**
     * @param \Workerman\Worker $worker
     * @return void
     */
    public static function start($worker)
    {
        $config = config('redis');
        static::$_manager = new RedisManager('', 'phpredis', $config);
    }

    /**
     * @param string $name
     * @return \Illuminate\Redis\Connections\Connection
     */
    public static function connection($name = 'default') {
        return static::$_manager->connection($name);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::$_manager->connection('default')->{$name}(... $arguments);
    }
}