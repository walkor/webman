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

use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use Webman\Bootstrap;
use Illuminate\Redis\RedisManager;

/**
 * Class Redis
 * @package support
 *
 * Strings methods
 * @method static int append($key, $value)
 * @method static int bitCount($key)
 * @method static int decr($key, $value)
 * @method static int decrBy($key, $value)
 * @method static string|bool get($key)
 * @method static int getBit($key, $offset)
 * @method static string getRange($key, $start, $end)
 * @method static string getSet($key, $value)
 * @method static int incr($key, $value)
 * @method static int incrBy($key, $value)
 * @method static float incrByFloat($key, $value)
 * @method static array mGet(array $keys)
 * @method static array getMultiple(array $keys)
 * @method static bool mSet($pairs)
 * @method static bool mSetNx($pairs)
 * @method static bool set($key, $val, $timeout = null)
 * @method static bool setBit($key, $offset, $value)
 * @method static bool setEx($key, $ttl, $value)
 * @method static bool pSetEx($key, $ttl, $value)
 * @method static bool setNx($key, $value)
 * @method static string setRange($key, $offset, $value)
 * @method static int strLen($key)
 * Keys methods
 * @method static int del(...$keys)
 * @method static int unlink(...$keys)
 * @method static false|string dump($key)
 * @method static int exists(...$keys)
 * @method static bool expire($key, $ttl)
 * @method static bool pexpire($key, $ttl)
 * @method static bool expireAt($key, $timestamp)
 * @method static bool pexpireAt($key, $timestamp)
 * @method static array keys($pattern)
 * @method static bool|array scan($it)
 * @method static void migrate($host, $port, $keys, $dbIndex, $timeout, $copy = false, $replace = false)
 * @method static bool move($key, $dbIndex)
 * @method static string|int|bool object($information, $key)
 * @method static bool persist($key)
 * @method static string randomKey()
 * @method static bool rename($srcKey, $dstKey)
 * @method static bool renameNx($srcKey, $dstKey)
 * @method static string type($key)
 * @method static int|array sort($key, $options = [])
 * @method static int ttl($key)
 * @method static int pttl($key)
 * @method static void restore($key, $ttl, $value)
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